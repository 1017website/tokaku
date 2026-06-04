<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        $plans = PricingPlan::ordered()->get();

        return view('superadmin.pricing.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->normalizePopular($data);

        PricingPlan::create($data);

        return back()->with('success', 'Paket harga berhasil ditambahkan.');
    }

    public function update(Request $request, PricingPlan $plan)
    {
        $data = $this->validateData($request);
        $this->normalizePopular($data, $plan->id);

        $plan->update($data);

        return back()->with('success', 'Paket harga berhasil diperbarui.');
    }

    public function toggle(PricingPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return back()->with('success', 'Status paket berhasil diubah.');
    }

    public function destroy(PricingPlan $plan)
    {
        $plan->delete();

        return back()->with('success', 'Paket harga berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'            => 'required|string|max:100',
            'tagline'         => 'nullable|string|max:150',
            'duration_months' => 'required|integer|min:1|max:120',
            'original_price'  => 'required|numeric|min:0',
            'price'           => 'required|numeric|min:0|lte:original_price',
            'is_popular'      => 'nullable|boolean',
            'is_active'       => 'nullable|boolean',
            'sort_order'      => 'nullable|integer|min:0',
            'cta_label'       => 'nullable|string|max:50',
        ], [
            'price.lte' => 'Harga diskon tidak boleh lebih besar dari harga asli.',
        ]);
    }

    /** Hanya boleh ada satu paket populer. */
    private function normalizePopular(array &$data, ?int $exceptId = null): void
    {
        $data['is_popular'] = (bool) ($data['is_popular'] ?? false);
        $data['is_active']  = (bool) ($data['is_active'] ?? true);
        $data['cta_label']  = $data['cta_label'] ?? 'Mulai Sekarang';

        if ($data['is_popular']) {
            PricingPlan::when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
                ->update(['is_popular' => false]);
        }
    }
}
