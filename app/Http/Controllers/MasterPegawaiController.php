<?php

namespace App\Http\Controllers;

use App\Models\MasterPegawai;
use App\Models\MasterDivisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $pegawai = MasterPegawai::with('divisi')
            ->when($q, fn($s) =>
                $s->where(function($w) use ($q){
                    $w->where('kode_pegawai','like',"%{$q}%")
                      ->orWhere('nama_pegawai','like',"%{$q}%");
                })
            )
            ->orderBy('kode_pegawai')
            ->paginate(10)
            ->withQueryString();

        $divisi = MasterDivisi::orderBy('nama_divisi')->get(['id_divisi','nama_divisi']);

        return view('master.pegawai.index', compact('pegawai','divisi','q'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_pegawai' => [
                'required','string','max:50',
                Rule::unique('master_pegawai','kode_pegawai')->whereNull('deleted_at'),
            ],
            'nama_pegawai' => ['required','string','max:150'],
            'id_divisi'    => ['required','exists:master_divisi,id_divisi'],
            'isactive'     => ['nullable','boolean'],
        ]);

        $validated['isactive'] = (bool) ($validated['isactive'] ?? false);

        DB::transaction(function () use ($validated) {
            
            $pegawai = MasterPegawai::create($validated);

            $username  = $validated['kode_pegawai'];
            $firstname = $validated['nama_pegawai'];
            
            if (User::where('username', $username)->exists()) {
                abort(422, 'Username sudah digunakan di tabel users.');
            }
            
            $base  = $username.'@pegawai.local';
            $email = $base;
            $i = 1;
            while (User::where('email', $email)->exists()) {
                $email = $username.'+'.$i.'@pegawai.local';
                $i++;
            }
            
            User::create([
                'username'  => $username,
                'firstname' => $firstname,
                'email'     => $email,
                'password'  => 'Rsudrat2025',
            ]);
        });

        return redirect()->route('pegawai.index')->with(
            'success',
            'Pegawai & akun login berhasil dibuat. Password default: <strong>Rsudrat2025</strong>.'
        );
    }

    public function edit(MasterPegawai $pegawai)
    {
        $divisi = MasterDivisi::orderBy('nama_divisi')->get(['id_divisi','nama_divisi']);
        return view('master.pegawai.edit', compact('pegawai','divisi'));
    }

    public function update(Request $request, MasterPegawai $pegawai)
    {
        $validated = $request->validate([
            'kode_pegawai' => [
                'required','string','max:50',
                Rule::unique('master_pegawai','kode_pegawai')
                    ->ignore($pegawai->id)
                    ->whereNull('deleted_at'),
            ],
            'nama_pegawai' => ['required','string','max:150'],
            'id_divisi'    => ['required','exists:master_divisi,id_divisi'],
            'isactive'     => ['nullable','boolean'],
        ]);

        $validated['isactive'] = (bool) ($validated['isactive'] ?? false);
        
        $oldKode = $pegawai->kode_pegawai;
        
        $pegawai->update($validated);
        
        $user = User::where('username', $oldKode)->first();

        if ($user) {
            
            $newUsername = $validated['kode_pegawai'];
            $newEmail    = $newUsername.'@pegawai.local';

            if ($newEmail !== $user->email) {
                $base = $newEmail; $email = $base; $i=1;
                while (User::where('email', $email)->where('id','!=',$user->id)->exists()) {
                    $email = $newUsername.'+'.$i.'@pegawai.local';
                    $i++;
                }
                $newEmail = $email;
            }

            $user->update([
                'username'  => $newUsername,
                'firstname' => $validated['nama_pegawai'],
                'email'     => $newEmail,
            ]);
        }

        return redirect()->route('pegawai.index')->with('success','Pegawai berhasil diperbarui.');
    }

    public function destroy(MasterPegawai $pegawai)
    {
        $pegawai->delete();
        return redirect()->route('pegawai.index')->with('success','Pegawai berhasil dihapus.');
    }

    public function toggle($id)
    {
        $p = MasterPegawai::findOrFail($id);
        $p->isactive = ! $p->isactive;
        $p->save();

        return back()->with('success','Status aktif berhasil diubah.');
    }
}
