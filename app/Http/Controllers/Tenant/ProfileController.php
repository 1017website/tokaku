<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $tenant = app('currentTenant');

        return view('tenant.profil.index', compact('tenant'));
    }

    public function update(Request $request)
    {
        $tenant = app('currentTenant');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_name' => 'nullable|string|max:50',
            'initial_capital' => 'nullable|numeric|min:0',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        $validated['tax_enabled'] = $request->boolean('tax_enabled');
        $validated['tax_rate'] = $request->tax_rate ?? 11;
        $validated['tax_name'] = $request->tax_name ?? 'PPN';
        $validated['initial_capital'] = $request->initial_capital ?? 0;

        if ($request->hasFile('logo')) {
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')
                ->store('tenant-logos', 'public');
        }

        unset($validated['logo']);
        $tenant->update($validated);

        return back()->with('success', 'Profil toko berhasil diperbarui.');
    }
}
