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
        $modules = array_keys(config('permissions.modules'));

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|max:255|not_regex:/\s/',
            'role'          => 'required|in:admin,cashier',
            'password'      => ['required', 'confirmed', Password::min(8)],
            'permissions'   => 'nullable|array',
            'permissions.*' => 'in:' . implode(',', $modules),
        ], [
            'email.required'  => 'Email / Username wajib diisi.',
            'email.not_regex' => 'Email / Username tidak boleh mengandung spasi.',
        ]);

        // Cek email unik per tenant
        $exists = User::where('tenant_id', $tenant->id)
            ->where('email', $request->email)
            ->exists();

        if ($exists) {
            return back()->withErrors(['email' => 'Email / Username ini sudah dipakai di toko Anda.'])->withInput();
        }

        // Batas jumlah user per tenant (termasuk owner)
        $maxUsers = (int) config('tokaku.max_users', 3);
        $currentCount = User::where('tenant_id', $tenant->id)->count();

        if ($currentCount >= $maxUsers) {
            return back()
                ->withErrors(['email' => "Maksimal {$maxUsers} user per toko (termasuk owner). Hapus atau nonaktifkan user lain terlebih dahulu."])
                ->withInput();
        }

        User::create([
            'tenant_id'   => $tenant->id,
            'name'        => $request->name,
            'email'       => $request->email,
            'role'        => $request->role,
            'permissions' => array_values((array) $request->input('permissions', [])),
            'password'    => Hash::make($request->password),
            'is_active'   => true,
        ]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function updatePermissions(Request $request, User $user)
    {
        abort_if((int) $user->tenant_id !== (int) app('currentTenant')->id, 403);
        abort_if($user->role === 'owner', 403, 'Owner selalu memiliki akses penuh.');

        $modules = array_keys(config('permissions.modules'));

        $request->validate([
            'role'          => 'required|in:admin,cashier',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'in:' . implode(',', $modules),
        ]);

        $user->update([
            'role'        => $request->role,
            'permissions' => array_values((array) $request->input('permissions', [])),
        ]);

        return back()->with('success', "Akses {$user->name} berhasil diperbarui.");
    }

    public function toggleActive(User $user)
    {
        // Pastikan user milik tenant ini
        abort_if((int) $user->tenant_id !== (int) app('currentTenant')->id, 403);

        // Tidak boleh nonaktifkan diri sendiri
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menonaktifkan akun sendiri.');

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        abort_if((int) $user->tenant_id !== (int) app('currentTenant')->id, 403);

        $request->validate([
            'password' => ['required', Password::min(8), 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Password {$user->name} berhasil diubah.");
    }

    public function destroy(User $user)
    {
        abort_if((int) $user->tenant_id !== (int) app('currentTenant')->id, 403);
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');
        abort_if($user->role === 'owner', 403, 'Owner tidak bisa dihapus.');

        $user->delete();

        return back()->with('success', "User {$user->name} berhasil dihapus.");
    }
}
