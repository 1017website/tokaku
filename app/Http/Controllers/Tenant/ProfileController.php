<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo'    => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')
                ->store('logos', 'local');
        }

        unset($validated['logo']);
        $tenant->update($validated);

        return back()->with('success', 'Profil toko berhasil diperbarui.');
    }
}
