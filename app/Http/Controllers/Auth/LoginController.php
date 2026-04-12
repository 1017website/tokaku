<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        // Coba login — tidak filter tenant_id di sini
        // Superadmin punya tenant_id null, tetap bisa login
        if (!Auth::attempt([
            'email'     => $request->email,
            'password'  => $request->password,
            'is_active' => true,
        ], $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah, atau akun tidak aktif.']);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // Superadmin → ke dashboard superadmin
        if ($user->role === 'superadmin') {
            return redirect()->intended(route('superadmin.dashboard'));
        }

        // Pastikan user punya tenant
        if (!$user->tenant_id) {
            Auth::logout();
            return back()->withErrors(['email' => 'Akun Anda tidak terhubung ke toko manapun.']);
        }

        return redirect()->intended(route('tenant.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
