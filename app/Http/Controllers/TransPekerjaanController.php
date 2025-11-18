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
        $bulanRaw = $request->get('bulan'); // dari <input type="month"> → "YYYY-MM"
        $divisi   = $request->get('id_divisi');

        // --- Hitung rentang tanggal untuk filter bulan ---
        $start = $end = null;
        if ($bulanRaw) {
            try {
                $start = \Carbon\Carbon::createFromFormat('Y-m', $bulanRaw)->startOfMonth();
                $end   = (clone $start)->endOfMonth();
            } catch (\Throwable $e) {
                // biarkan null jika format tak valid
            }
        }

        $isAdmin = auth()->check() && auth()->user()->username === 'admin';

        // Pegawai & divisi milik user login (kalau bukan admin)
        $pegawaiLogin = null;
        $divisiLogin = null;
        if (!$isAdmin) {
            $pegawaiLogin = \App\Models\MasterPegawai::where('kode_pegawai', auth()->user()->username)->first()
                ?? (isset(auth()->user()->pegawai_id) ? \App\Models\MasterPegawai::find(auth()->user()->pegawai_id) : null);
            $divisiLogin  = $pegawaiLogin ? \App\Models\MasterDivisi::find($pegawaiLogin->id_divisi) : null;
        }

        // Dropdown pegawai & divisi
        $pegawais = \App\Models\MasterPegawai::orderBy('nama_pegawai')->get(['id', 'nama_pegawai', 'id_divisi']);

        $divisis = \App\Models\MasterDivisi::query()
            ->when(!$isAdmin && $divisiLogin, function ($q2) use ($divisiLogin) {
                $q2->where('id_divisi', $divisiLogin->id_divisi)
                    ->orWhereRaw('LOWER(nama_divisi)=?', ['all']);
            })
            ->orderBy('nama_divisi')
            ->get(['id_divisi', 'nama_divisi']);

        // Jika non-admin memilih id_divisi yang tidak diizinkan → abaikan
        if (!$isAdmin && $divisi) {
            $allowed = $divisis->pluck('id_divisi')->all();
            if (!in_array($divisi, $allowed)) $divisi = null;
        }

        $data = \App\Models\TransPekerjaan::with([
            'pegawais:id,nama_pegawai,id_divisi', // ❗ many-to-many
            'divisi:id_divisi,nama_divisi',
            'fotos' => fn($q) => $q->orderBy('sort'),
        ])
            // Non-admin: hanya pekerjaan yang mencantumkan dia sebagai pegawai
            ->when(
                !$isAdmin && $pegawaiLogin,
                fn($s) =>
                $s->whereHas('pegawais', fn($p) => $p->where('master_pegawai.id', $pegawaiLogin->id))
            )
            // Search: judul/detail atau nama pegawai
            ->when($q, function ($s) use ($q) {
                $s->where(function ($w) use ($q) {
                    $w->where('judul_pekerjaan', 'like', "%{$q}%")
                        ->orWhere('detail_pekerjaan', 'like', "%{$q}%")
                        ->orWhereHas('pegawais', fn($p) => $p->where('nama_pegawai', 'like', "%{$q}%"));
                });
            })
            // Filter bulan: rentang tanggal (kolom 'bulan' bertipe DATE)
            ->when(
                $start && $end,
                fn($s) =>
                $s->whereBetween('bulan', [$start->toDateString(), $end->toDateString()])
            )
            // Filter divisi: pakai kolom id_divisi di pekerjaan (bukan di pegawai)
            ->when($divisi, fn($s) => $s->where('id_divisi', $divisi))
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        // untuk isi default <input type="month">
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
        if (!$pegawaiLogin || $pekerjaan->pegawai_id !== $pegawaiLogin->id) {
            abort(403, 'Anda tidak berhak mengakses data ini.');
        }
    }


    public function store(Request $request)
    {
        $isAdmin = $this->isAdmin();

        // --- Validasi ---
        $rules = [
            'judul_pekerjaan'  => ['required', 'string', 'max:200'],
            'detail_pekerjaan' => ['required', 'string', 'max:2000'],
            'bulan'            => ['required', 'date', 'before_or_equal:today'], // <= hari ini
            // banyak pegawai (untuk admin & non-admin)
            'pegawai_id'       => ['required', 'array', 'min:1'],
            'pegawai_id.*'     => ['integer', 'exists:master_pegawai,id'],

            'foto_sebelum.*'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'foto_sesudah.*'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
        if ($isAdmin) {
            $rules['id_divisi'] = ['required', 'exists:master_divisi,id_divisi'];
        }

        $validated = $request->validate($rules);

        // --- Tentukan divisi final ---
        if ($isAdmin) {
            $divisiId = (int) $validated['id_divisi'];
        } else {
            $loginPegawai = $this->getLoginPegawai();
            if (!$loginPegawai) {
                return back()->with('error', 'Akun login belum terhubung ke data pegawai.')->withInput();
            }
            $divisiId = $loginPegawai->id_divisi;
        }

        // --- Normalisasi array file (kalau single akan jadi array juga) ---
        $filesSebelum = $request->file('foto_sebelum', []);
        if ($filesSebelum instanceof \Illuminate\Http\UploadedFile) $filesSebelum = [$filesSebelum];

        $filesSesudah = $request->file('foto_sesudah', []);
        if ($filesSesudah instanceof \Illuminate\Http\UploadedFile) $filesSesudah = [$filesSesudah];

        // --- Simpan transaksi atomik ---
        DB::transaction(function () use ($validated, $divisiId, $filesSebelum, $filesSesudah) {
            // 1) Buat 1 baris pekerjaan
            $pekerjaan = TransPekerjaan::create([
                'judul_pekerjaan'  => $validated['judul_pekerjaan'],
                'detail_pekerjaan' => $validated['detail_pekerjaan'],
                'bulan'            => $validated['bulan'],    // tipe date (Y-m-d)
                'id_divisi'        => $divisiId,
            ]);

            // 2) Relasi banyak pegawai (pivot) – unik & tersortir
            $pegawaiIds = array_values(array_unique(array_map('intval', $validated['pegawai_id'])));
            $pekerjaan->pegawais()->sync($pegawaiIds);

            // 3) Upload foto "sebelum"
            $i = 0;
            foreach ($filesSebelum as $f) {
                if (!$f || !$f->isValid()) continue;
                $path = $f->store('trans_pekerjaan', 'public');
                TransPekerjaanFoto::create([
                    'pekerjaan_id' => $pekerjaan->id,
                    'kategori'     => 'sebelum',
                    'path'         => $path,
                    'sort'         => $i++,
                ]);
            }

            // 4) Upload foto "sesudah"
            $i = 0;
            foreach ($filesSesudah as $f) {
                if (!$f || !$f->isValid()) continue;
                $path = $f->store('trans_pekerjaan', 'public');
                TransPekerjaanFoto::create([
                    'pekerjaan_id' => $pekerjaan->id,
                    'kategori'     => 'sesudah',
                    'path'         => $path,
                    'sort'         => $i++,
                ]);
            }
        });

        return redirect()
            ->route('trans.pekerjaan.index')
            ->with('success', 'Transaksi pekerjaan berhasil ditambahkan.');
    }


    public function daftar(Request $request)
    {
        $q        = $request->get('q');
        $bulanRaw = $request->get('bulan');   // "YYYY-MM" dari input month
        $divisi   = $request->get('id_divisi');

        $start = $end = null;
        if ($bulanRaw) {
            try {
                $start = \Carbon\Carbon::createFromFormat('Y-m', $bulanRaw)->startOfMonth();
                $end   = (clone $start)->endOfMonth();
            } catch (\Throwable $e) {
            }
        }

        $pegawais = \App\Models\MasterPegawai::orderBy('nama_pegawai')->get(['id', 'nama_pegawai', 'id_divisi']);
        $divisis  = \App\Models\MasterDivisi::orderBy('nama_divisi')->get(['id_divisi', 'nama_divisi']);

        $data = \App\Models\TransPekerjaan::with(['pegawai', 'divisi', 'fotos'])
            ->when($q, function ($s) use ($q) {
                $s->where(function ($w) use ($q) {
                    $w->where('judul_pekerjaan', 'like', "%{$q}%")
                        ->orWhere('detail_pekerjaan', 'like', "%{$q}%")
                        ->orWhereHas('pegawai', fn($p) => $p->where('nama_pegawai', 'like', "%{$q}%"));
                });
            })
            ->when($start && $end, fn($s) => $s->whereBetween('bulan', [$start->toDateString(), $end->toDateString()]))
            ->when($divisi, fn($s) => $s->where('id_divisi', $divisi))
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $isAdmin = auth()->check() && auth()->user()->username === 'admin';
        $bulan   = $bulanRaw ?: now()->format('Y-m');

        return view('trans.pekerjaan.daftar', compact('data', 'pegawais', 'divisis', 'q', 'bulan', 'divisi', 'isAdmin'));
    }



    public function edit(TransPekerjaan $pekerjaan)
    {
        $this->ensureCanAccess($pekerjaan);

        $pegawais = MasterPegawai::orderBy('nama_pegawai')->get(['id', 'nama_pegawai', 'id_divisi']);
        $divisis  = MasterDivisi::orderBy('nama_divisi')->get(['id_divisi', 'nama_divisi']);

        $isAdmin = $this->isAdmin();

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
        if ($this->isAdmin()) $rules['id_divisi'] = ['required', 'exists:master_divisi,id_divisi'];
        $validated = $request->validate($rules);

        // Tentukan pegawai/divisi final
        if ($this->isAdmin()) $divisiId = (int)$validated['id_divisi'];
        else {
            $login = $this->getLoginPegawai();
            if (!$login) return back()->with('error', 'Akun login belum terhubung pegawai.')->withInput();
            $divisiId = $login->id_divisi;
        }

        DB::transaction(function () use ($request, $pekerjaan, $validated, $divisiId) {
            $pekerjaan->update([
                'judul_pekerjaan'  => $validated['judul_pekerjaan'],
                'detail_pekerjaan' => $validated['detail_pekerjaan'],
                'bulan'            => $validated['bulan'],
                'id_divisi'        => $divisiId,
            ]);

            $ids = array_values(array_unique(array_map('intval', $validated['pegawai_id'])));
            $pekerjaan->pegawais()->sync($ids);

            foreach (['sebelum' => 'foto_sebelum', 'sesudah' => 'foto_sesudah'] as $kat => $field) {
                if ($request->hasFile($field)) {
                    $start = TransPekerjaanFoto::where('pekerjaan_id', $pekerjaan->id)->where('kategori', $kat)->max('sort');
                    $i = is_null($start) ? 0 : $start + 1;
                    foreach ($request->file($field) as $file) {
                        if (!$file) continue;
                        $path = $file->store('trans_pekerjaan', 'public');
                        TransPekerjaanFoto::create([
                            'pekerjaan_id' => $pekerjaan->id,
                            'kategori'    => $kat,
                            'path'        => $path,
                            'sort'        => $i++,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('trans.pekerjaan.index')->with('success', 'Transaksi pekerjaan berhasil diperbarui.');
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

    /* ================== Helper ================== */

    private function isAdmin(): bool
    {
        return Auth::check() && Auth::user()->username === 'admin';
    }

    /**
     * Pegawai yang terhubung dengan akun login saat ini.
     */
    private function getLoginPegawai(): ?MasterPegawai
    {
        $user = Auth::user();
        if (!$user) return null;

        return MasterPegawai::where('kode_pegawai', $user->username)->first()
            ?? (isset($user->pegawai_id) ? MasterPegawai::find($user->pegawai_id) : null);
    }

    /**
     * Pastikan user boleh mengakses (edit/hapus) pekerjaan ini.
     * Admin: selalu boleh.
     * Non-admin: hanya jika pekerjaan miliknya sendiri (pegawai_id sama).
     */


    public function destroyFoto(TransPekerjaanFoto $foto)
    {
        Storage::disk('public')->delete($foto->path);
        $foto->delete();
        return back()->with('success', 'Foto berhasil dihapus.');
    }
}
