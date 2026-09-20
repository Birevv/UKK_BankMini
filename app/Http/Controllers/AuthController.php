<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            $role = Auth::user()->role;

            if ($role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            } elseif ($role === 'teller') {
                return redirect()->intended('/teller/dashboard');
            } elseif ($role === 'supervisor') {
                return redirect()->intended('/supervisor/dashboard');
            } elseif ($role === 'nasabah') {
                return redirect()->intended('/nasabah/dashboard');
            }
        }

        return back()->with('error', 'Username atau Password salah!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda berhasil logout.');
    }
}
