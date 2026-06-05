<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'owner_name'    => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email',
            'phone'         => 'required|string|max:20',
            'business_name' => 'required|string|max:255',  // nama usaha
            'business_type' => 'required|string|max:100',   // jenis usaha
            'password'      => 'required|min:8|confirmed',
        ], [
            'owner_name.required'    => 'Nama wajib diisi.',
            'email.required'         => 'Email wajib diisi.',
            'email.unique'           => 'Email sudah terdaftar.',
            'phone.required'         => 'Nomor HP wajib diisi.',
            'business_name.required' => 'Nama usaha wajib diisi.',
            'business_type.required' => 'Jenis usaha wajib diisi.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
            'password.min'           => 'Password minimal 8 karakter.',
        ]);

        // Buat subdomain unik dari nama usaha (untuk konsistensi data, walau
        // resolusi tenant tetap berbasis login).
        $base = Str::slug($validated['business_name']) ?: 'toko';
        $subdomain = $base;
        $i = 1;
        while (Tenant::where('subdomain', $subdomain)->exists()) {
            $subdomain = $base . '-' . $i++;
        }

        $tenant = Tenant::create([
            'name'          => $validated['business_name'],
            'subdomain'     => $subdomain,
            'phone'         => $validated['phone'],
            'business_type' => $validated['business_type'],
            'owner_name'    => $validated['owner_name'],
            'owner_email'   => $validated['email'],
            'status'        => 'pending', // menunggu persetujuan superadmin
        ]);

        // User owner dibuat nonaktif sampai disetujui.
        User::create([
            'tenant_id' => $tenant->id,
            'name'      => $validated['owner_name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'owner',
            'is_active' => true, // login diblokir oleh status tenant 'pending', bukan flag ini
        ]);

        return redirect()->route('register')->with('registered', true);
    }
}
