<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function form()
    {
        return view('auth.masuk');
    }

    public function masuk(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $kunci = 'masuk:' . strtolower($data['email']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($kunci) . ' detik.',
            ]);
        }

        if (! Auth::attempt($data + ['aktif' => true], $request->boolean('ingat'))) {
            RateLimiter::hit($kunci, 60);
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi salah, atau akun dinonaktifkan.']);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dasbor'));
    }

    public function keluar(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda');
    }
}
