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
            'email'    => 'required|string',
            'password' => 'required',
        ], [
            'email.required'    => 'Email / Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        // Login cocokkan kolom 'email' (bisa berisi email atau username).
        // Superadmin punya tenant_id null, tetap bisa login.
        if (!Auth::attempt([
            'email'     => $request->email,
            'password'  => $request->password,
            'is_active' => true,
        ], $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email/Username atau password salah, atau akun tidak aktif.']);
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

        $user->load('tenant');

        // Jika trial sudah habis / tenant suspended, user tenant tidak boleh login.
        // Contoh: tenant diberi trial 3 hari, setelah trial_ends_at lewat maka login langsung ditolak.
        if (!$user->tenant || !$user->tenant->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Masa trial atau langganan toko Anda sudah berakhir. Silakan hubungi administrator.';
            if ($user->tenant && $user->tenant->status === 'suspended') {
                $message = 'Akun toko Anda sedang ditangguhkan. Silakan hubungi administrator.';
            } elseif ($user->tenant && $user->tenant->status === 'pending') {
                $message = 'Pendaftaran Anda sedang menunggu persetujuan administrator. Kami akan menghubungi Anda segera.';
            } elseif ($user->tenant && $user->tenant->status === 'rejected') {
                $message = 'Maaf, pendaftaran Anda belum dapat disetujui. Silakan hubungi administrator untuk informasi lebih lanjut.';
            }

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        return redirect()->intended($user->homeRoute());
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
