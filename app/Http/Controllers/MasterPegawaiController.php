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
            // 1) Simpan pegawai
            $pegawai = MasterPegawai::create($validated);

            // 2) Siapkan credential user
            $username  = $validated['kode_pegawai'];      // login pakai username
            $firstname = $validated['nama_pegawai'];

            // Pastikan username unik (disarankan, karena tabel users kamu tidak unique di username)
            if (User::where('username', $username)->exists()) {
                abort(422, 'Username sudah digunakan di tabel users.');
            }

            // Email unik berbasis kode_pegawai
            $base  = $username.'@pegawai.local';
            $email = $base;
            $i = 1;
            while (User::where('email', $email)->exists()) {
                $email = $username.'+'.$i.'@pegawai.local';
                $i++;
            }

            // 3) Buat user (password plain → dimutasi oleh setPasswordAttribute() menjadi bcrypt)
            User::create([
                'username'  => $username,
                'firstname' => $firstname,
                'email'     => $email,
                'password'  => 'Rsudrat2025',   // JANGAN Hash::make — mutator di model sudah nge-bcrypt
                // kolom lain biarkan null
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

        // simpan kode lama buat sinkron user
        $oldKode = $pegawai->kode_pegawai;

        // Update pegawai
        $pegawai->update($validated);

        // Sinkron ke users: cari user berdasar username lama
        $user = User::where('username', $oldKode)->first();

        if ($user) {
            // Siapkan email baru (berdasar kode baru) dan pastikan unik
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
        $pegawai->delete(); // soft delete
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
