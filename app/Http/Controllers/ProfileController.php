<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'username'  => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'email'     => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'firstname' => ['nullable', 'string', 'max:100'],
            'lastname'  => ['nullable', 'string', 'max:100'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
        ]);

        // JANGAN Hash::make di sini, cukup assign plain text → mutator akan bcrypt
        $user->password = $request->password;
        $user->save();

        // Optional: kalau sebelumnya login pakai "Remember me", refresh token biar sesi konsisten
        // $request->session()->regenerate();

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
