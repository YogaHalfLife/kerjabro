<?php

namespace App\Http\Controllers;

use App\Models\TransPekerjaan;
use App\Models\TransPekerjaanFoto;
use App\Models\MasterPegawai;
use App\Models\MasterDivisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class TransPekerjaanController extends Controller
{
    public function index(Request $request)
    {
        $q        = $request->get('q');
        $bulanRaw = $request->get('bulan');
        $divisi   = $request->get('id_divisi');

        $start = $end = null;
        if ($bulanRaw) {
            try {
                $start = \Carbon\Carbon::createFromFormat('Y-m', $bulanRaw)->startOfMonth();
                $end   = (clone $start)->endOfMonth();
            } catch (\Throwable $e) {
            }
        }

        $isAdmin = auth()->check() && auth()->user()->username === 'admin';

        $pegawaiLogin = null;
        $divisiLogin  = null;
        if (!$isAdmin) {
            $pegawaiLogin = \App\Models\MasterPegawai::where('kode_pegawai', auth()->user()->username)->first()
                ?? (isset(auth()->user()->pegawai_id) ? \App\Models\MasterPegawai::find(auth()->user()->pegawai_id) : null);
            $divisiLogin  = $pegawaiLogin ? \App\Models\MasterDivisi::find($pegawaiLogin->id_divisi) : null;
        }

        $pegawais = \App\Models\MasterPegawai::orderBy('nama_pegawai')->get(['id', 'nama_pegawai', 'id_divisi']);

        $divisis = \App\Models\MasterDivisi::query()
            ->when(!$isAdmin && $divisiLogin, function ($q2) use ($divisiLogin) {
                $q2->where('id_divisi', $divisiLogin->id_divisi)
                    ->orWhereRaw('LOWER(nama_divisi)=?', ['all']);
            })
            ->orderBy('nama_divisi')
            ->get(['id_divisi', 'nama_divisi']);

        if (!$isAdmin && $divisi) {
            $allowed = $divisis->pluck('id_divisi')->all();
            if (!in_array($divisi, $allowed)) $divisi = null;
        }

        $data = \App\Models\TransPekerjaan::with([
            'pegawais:id,nama_pegawai,id_divisi',
            'divisi:id_divisi,nama_divisi',
            'fotos' => fn($q) => $q->orderBy('sort'),
        ])

            ->when(
                !$isAdmin && $pegawaiLogin,
                fn($s) =>
                $s->whereHas('pegawais', fn($p) => $p->where('master_pegawai.id', $pegawaiLogin->id))
            )
            
            ->when($q, function ($s) use ($q) {
                $s->where(function ($w) use ($q) {
                    $w->where('judul_pekerjaan', 'like', "%{$q}%")
                        ->orWhere('detail_pekerjaan', 'like', "%{$q}%")
                        ->orWhereHas('pegawais', fn($p) => $p->where('nama_pegawai', 'like', "%{$q}%"));
                });
            })
            
            ->when(
                $start && $end,
                fn($s) =>
                $s->whereBetween('bulan', [$start->toDateString(), $end->toDateString()])
            )
            
            ->when($divisi, function ($s) use ($divisi) {
                $s->where(function ($w) use ($divisi) {
                    $w->where('id_divisi', $divisi)
                        ->orWhereHas('pegawais', function ($p) use ($divisi) {
                            $p->where('master_pegawai.id_divisi', $divisi);
                        });
                });
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();
            
        $bulan = $bulanRaw ?: now()->format('Y-m');

        return view('trans.pekerjaan.index', compact(
            'data',
            'pegawais',
            'divisis',
            'q',
            'bulan',
            'divisi',
            'pegawaiLogin',
            'divisiLogin',
            'isAdmin'
        ));
    }


    private function ensureCanAccess(TransPekerjaan $pekerjaan): void
    {
        if ($this->isAdmin()) {
            return;
        }
        
        $pegawaiLogin = $this->getLoginPegawai();
        if (!$pegawaiLogin) {
            abort(403, 'Akun login belum terhubung ke data pegawai.');
        }
        
        $boleh = $pekerjaan->pegawais()
            ->where('master_pegawai.id', $pegawaiLogin->id)
            ->exists();

        if (!$boleh) {
            abort(403, 'Anda tidak berhak mengakses data ini.');
        }
    }



    public function store(Request $request)
    {
        $isAdmin = Auth::check() && Auth::user()->username === 'admin';
        
        $rules = [
            'judul_pekerjaan'  => ['required', 'string', 'max:200'],
            'detail_pekerjaan' => ['required', 'string', 'max:2000'],
            'bulan'            => ['required', 'date'],

            'foto_sebelum.*'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'foto_sesudah.*'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            
            'pegawai_id'       => ['required', 'array', 'min:1'],
            'pegawai_id.*'     => ['required', 'exists:master_pegawai,id'],
        ];

        if ($isAdmin) {
            $rules['id_divisi'] = ['nullable', 'exists:master_divisi,id_divisi'];
        }

        $validated = $request->validate($rules);

        $divisiId = $isAdmin
            ? ($validated['id_divisi'] ?? null)
            : optional($this->getLoginPegawai())->id_divisi;

        DB::transaction(function () use ($request, $validated, $divisiId) {

            $pekerjaan = TransPekerjaan::create([
                'judul_pekerjaan'  => $validated['judul_pekerjaan'],
                'detail_pekerjaan' => $validated['detail_pekerjaan'],
                'bulan'            => $validated['bulan'],
                'id_divisi'        => $divisiId,
            ]);
            
            $pegawaiIds = $validated['pegawai_id'];

            $pegawaiList = MasterPegawai::whereIn('id', $pegawaiIds)
                ->get(['id', 'id_divisi']);
                
            $pivotData = [];
            foreach ($pegawaiList as $peg) {
                $pivotData[$peg->id] = [
                    'id_divisi' => $peg->id_divisi,
                ];
            }
            
            $pekerjaan->pegawais()->sync($pivotData);
            
            foreach (['sebelum' => 'foto_sebelum', 'sesudah' => 'foto_sesudah'] as $kategori => $field) {
                $files = $request->file($field, []);
                
                if ($files instanceof \Illuminate\Http\UploadedFile) {
                    $files = [$files];
                }

                $i = 0;
                foreach ($files as $file) {
                    if (!$file || !$file->isValid()) {
                        continue;
                    }

                    $path = $file->store('trans_pekerjaan', 'public');

                    TransPekerjaanFoto::create([
                        'pekerjaan_id' => $pekerjaan->id,
                        'kategori'     => $kategori,
                        'path'         => $path,
                        'sort'         => $i++,
                    ]);
                }
            }
        });

        return back()->with('success', 'Transaksi pekerjaan berhasil ditambahkan.');
    }

    public function daftar(Request $request)
    {
        $q        = $request->get('q');
        $bulanRaw = $request->get('bulan');
        $divisi   = $request->get('id_divisi');
        
        $start = $end = null;
        if ($bulanRaw) {
            try {
                $start = \Carbon\Carbon::createFromFormat('Y-m', $bulanRaw)->startOfMonth();
                $end   = (clone $start)->endOfMonth();
            } catch (\Throwable $e) {
                
            }
        }
        
        $pegawais = \App\Models\MasterPegawai::orderBy('nama_pegawai')
            ->get(['id', 'nama_pegawai', 'id_divisi']);

        $divisis  = \App\Models\MasterDivisi::orderBy('nama_divisi')
            ->get(['id_divisi', 'nama_divisi']);
            
        $data = \App\Models\TransPekerjaan::with([
            'pegawais',
            'divisi',
            'divisis',
            'fotos',
        ])
            ->when($q, function ($s) use ($q) {
                $s->where(function ($w) use ($q) {
                    $w->where('judul_pekerjaan', 'like', "%{$q}%")
                        ->orWhere('detail_pekerjaan', 'like', "%{$q}%")
                        ->orWhereHas('pegawais', fn($p) => $p->where('nama_pegawai', 'like', "%{$q}%"));
                });
            })
            ->when(
                $start && $end,
                fn($s) =>
                $s->whereBetween('bulan', [$start->toDateString(), $end->toDateString()])
            )
            
            ->when($divisi, function ($s) use ($divisi) {
                $s->where(function ($w) use ($divisi) {
                    $w->where('id_divisi', $divisi)
                        ->orWhereHas('pegawais', function ($p) use ($divisi) {
                            $p->where('master_pegawai.id_divisi', $divisi);
                        });
                });
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $isAdmin = auth()->check() && auth()->user()->username === 'admin';
        $bulan   = $bulanRaw ?: now()->format('Y-m');

        return view('trans.pekerjaan.daftar', compact(
            'data',
            'pegawais',
            'divisis',
            'q',
            'bulan',
            'divisi',
            'isAdmin'
        ));
    }


    public function edit(TransPekerjaan $pekerjaan)
    {
        $this->ensureCanAccess($pekerjaan);

        $isAdmin = $this->isAdmin();

        $pegawais = MasterPegawai::orderBy('nama_pegawai')
            ->get(['id', 'nama_pegawai', 'id_divisi']);

        $divisis  = MasterDivisi::orderBy('nama_divisi')
            ->get(['id_divisi', 'nama_divisi']);

        $pegawaiLogin = null;
        $divisiLogin  = null;
        if (!$isAdmin) {
            $pegawaiLogin = $this->getLoginPegawai();
            $divisiLogin  = $pegawaiLogin ? MasterDivisi::find($pegawaiLogin->id_divisi) : null;
        }

        $pekerjaan->load(['pegawais', 'fotosSebelum', 'fotosSesudah']);

        return view('trans.pekerjaan.edit', compact(
            'pekerjaan',
            'pegawais',
            'divisis',
            'isAdmin',
            'pegawaiLogin',
            'divisiLogin'
        ));
    }


    public function update(Request $request, TransPekerjaan $pekerjaan)
    {
        $this->ensureCanAccess($pekerjaan);
        $isAdmin = $this->isAdmin();

        $rules = [
            'judul_pekerjaan'  => ['required', 'string', 'max:200'],
            'detail_pekerjaan' => ['required', 'string', 'max:2000'],
            'bulan'            => ['required', 'date'],
            'pegawai_id'       => ['required', 'array', 'min:1'],
            'pegawai_id.*'     => ['integer', 'exists:master_pegawai,id'],
            'foto_sebelum.*'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'foto_sesudah.*'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];

        if ($isAdmin) {
            $rules['id_divisi'] = ['required', 'exists:master_divisi,id_divisi'];
        }

        $validated = $request->validate($rules);
        
        if ($isAdmin) {
            $divisiId = (int) $validated['id_divisi'];
        } else {
            $login = $this->getLoginPegawai();
            if (!$login) {
                return back()
                    ->with('error', 'Akun login belum terhubung pegawai.')
                    ->withInput();
            }
            $divisiId = $login->id_divisi;
        }

        DB::transaction(function () use ($request, $pekerjaan, $validated, $divisiId) {
            $pekerjaan->update([
                'judul_pekerjaan'  => $validated['judul_pekerjaan'],
                'detail_pekerjaan' => $validated['detail_pekerjaan'],
                'bulan'            => $validated['bulan'],
                'id_divisi'        => $divisiId,
            ]);
            
            $pegawaiIds = array_values(array_unique(array_map('intval', $validated['pegawai_id'])));

            $pegawaiList = MasterPegawai::whereIn('id', $pegawaiIds)
                ->get(['id', 'id_divisi']);

            $pivotData = [];
            foreach ($pegawaiList as $peg) {
                $pivotData[$peg->id] = [
                    'id_divisi' => $peg->id_divisi,
                ];
            }

            $pekerjaan->pegawais()->sync($pivotData);
            foreach (['sebelum' => 'foto_sebelum', 'sesudah' => 'foto_sesudah'] as $kat => $field) {
                if ($request->hasFile($field)) {
                    $start = TransPekerjaanFoto::where('pekerjaan_id', $pekerjaan->id)
                        ->where('kategori', $kat)
                        ->max('sort');

                    $i = is_null($start) ? 0 : $start + 1;

                    foreach ($request->file($field) as $file) {
                        if (!$file) {
                            continue;
                        }
                        $path = $file->store('trans_pekerjaan', 'public');
                        TransPekerjaanFoto::create([
                            'pekerjaan_id' => $pekerjaan->id,
                            'kategori'     => $kat,
                            'path'         => $path,
                            'sort'         => $i++,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('trans.pekerjaan.index')
            ->with('success', 'Transaksi pekerjaan berhasil diperbarui.');
    }


    public function destroy(TransPekerjaan $pekerjaan)
    {
        $this->ensureCanAccess($pekerjaan);

        DB::transaction(function () use ($pekerjaan) {
            foreach ($pekerjaan->fotos as $f) {
                Storage::disk('public')->delete($f->path);
                $f->delete();
            }
            $pekerjaan->delete();
        });

        return back()->with('success', 'Transaksi pekerjaan berhasil dihapus.');
    }

    private function isAdmin(): bool
    {
        return Auth::check() && Auth::user()->username === 'admin';
    }

    private function getLoginPegawai(): ?MasterPegawai
    {
        $user = Auth::user();
        if (!$user) return null;

        return MasterPegawai::where('kode_pegawai', $user->username)->first()
            ?? (isset($user->pegawai_id) ? MasterPegawai::find($user->pegawai_id) : null);
    }
    
    public function destroyFoto(TransPekerjaanFoto $foto)
    {
        Storage::disk('public')->delete($foto->path);
        $foto->delete();
        return back()->with('success', 'Foto berhasil dihapus.');
    }
}
