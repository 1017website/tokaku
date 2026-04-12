<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function index()
    {
        $totalTenants  = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $trialTenants  = Tenant::where('status', 'trial')->count();

        return view('superadmin.dashboard', compact(
            'totalTenants',
            'activeTenants',
            'trialTenants',
        ));
    }

    public function tenants()
    {
        $tenants = Tenant::withCount('users')->orderByDesc('created_at')->paginate(20);

        return view('superadmin.tenants.index', compact('tenants'));
    }

    public function storeTenant(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'subdomain' => 'required|string|max:50|unique:tenants,subdomain|alpha_dash',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'required|email',
            'password'  => 'required|min:8',
        ]);

        $tenant = Tenant::create([
            'name'          => $validated['name'],
            'subdomain'     => $validated['subdomain'],
            'phone'         => $validated['phone'] ?? null,
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Owner ' . $tenant->name,
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        return back()->with('success', "Tenant {$tenant->name} berhasil dibuat. Trial 14 hari dimulai.");
    }

    public function suspend(Tenant $tenant)
    {
        $status = $tenant->status === 'suspended' ? 'active' : 'suspended';
        $tenant->update(['status' => $status]);

        $label = $status === 'suspended' ? 'ditangguhkan' : 'diaktifkan kembali';

        return back()->with('success', "Tenant {$tenant->name} berhasil {$label}.");
    }
}
