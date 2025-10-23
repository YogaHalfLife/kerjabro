<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;

class LoginController extends Controller
{
    /**
     * Display login page.
     *
     * @return Renderable
     */
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],      // atau: 'required','alpha_dash','min:3'
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(
            ['username' => $validated['username'], 'password' => $validated['password']],
            $remember
        )) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()
            ->withErrors(['username' => 'The provided credentials do not match our records.'])
            ->onlyInput('username');
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
