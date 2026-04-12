<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('tenant_id', app('currentTenant')->id)
            ->orderByRaw("FIELD(role,'owner','admin','cashier')")
            ->orderBy('name')
            ->get();

        return view('tenant.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $tenant = app('currentTenant');

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'role'     => 'required|in:admin,cashier',
            'password' => ['required', Password::min(8)],
        ]);

        // Cek email unik per tenant
        $exists = User::where('tenant_id', $tenant->id)
            ->where('email', $request->email)
            ->exists();

        if ($exists) {
            return back()->withErrors(['email' => 'Email ini sudah dipakai di toko Anda.'])->withInput();
        }

        User::create([
            'tenant_id' => $tenant->id,
            'name'      => $request->name,
            'email'     => $request->email,
            'role'      => $request->role,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function toggleActive(User $user)
    {
        // Pastikan user milik tenant ini
        abort_if($user->tenant_id !== app('currentTenant')->id, 403);

        // Tidak boleh nonaktifkan diri sendiri
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menonaktifkan akun sendiri.');

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        abort_if($user->tenant_id !== app('currentTenant')->id, 403);

        $request->validate([
            'password' => ['required', Password::min(8), 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Password {$user->name} berhasil diubah.");
    }

    public function destroy(User $user)
    {
        abort_if($user->tenant_id !== app('currentTenant')->id, 403);
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');
        abort_if($user->role === 'owner', 403, 'Owner tidak bisa dihapus.');

        $user->delete();

        return back()->with('success', "User {$user->name} berhasil dihapus.");
    }
}
