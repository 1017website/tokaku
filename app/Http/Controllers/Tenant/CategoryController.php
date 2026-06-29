<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'active'); // active | inactive | all

        $categories = Category::withCount('products')
            ->when($status === 'active',   fn($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', false))
            ->ordered()
            ->get();

        // Daftar kategori non-pinned & aktif sebagai opsi target "Menu Tetap".
        $targetOptions = Category::active()->where('is_pinned', false)->ordered()->get();

        return view('tenant.categories.index', compact('categories', 'status', 'targetOptions'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Category::create($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateData($request);

        $category->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * "Hapus" = nonaktifkan kategori (soft), bukan menghapus row.
     * Produk & riwayat transaksi tetap utuh; kategori nonaktif tidak
     * muncul di kasir dan tab Aktif. Bisa diaktifkan kembali.
     */
    public function destroy(Category $category)
    {
        abort_if($category->tenant_id != app('currentTenant')->id, 403);

        $category->update(['is_active' => false]);

        return back()->with('success', 'Kategori berhasil dinonaktifkan.');
    }

    public function activate(Category $category)
    {
        abort_if($category->tenant_id != app('currentTenant')->id, 403);

        $category->update(['is_active' => true]);

        return back()->with('success', 'Kategori berhasil diaktifkan kembali.');
    }

    /**
     * Validasi + normalisasi. Masa berlaku hanya untuk promo/bundling;
     * untuk regular kita kosongkan agar konsisten.
     */
    protected function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'type'              => 'required|in:regular,promo,bundling',
            'starts_at'         => 'nullable|date',
            'ends_at'           => 'nullable|date|after_or_equal:starts_at',
            'sort_order'        => 'nullable|integer|min:0|max:9999',
            'is_pinned'         => 'nullable|boolean',
            'schedule_days'     => 'nullable|array',
            'schedule_days.*'   => 'integer|min:1|max:7',
            'pinned_targets'    => 'nullable|array',
            'pinned_targets.*'  => 'integer|exists:categories,id',
        ], [
            'ends_at.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
        ]);

        if ($validated['type'] === 'regular') {
            $validated['starts_at'] = null;
            $validated['ends_at'] = null;
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_pinned']  = $request->boolean('is_pinned');

        // Normalisasi jadwal hari: unik, terurut, dan kosong = null (tampil tiap hari).
        $days = array_values(array_unique(array_map('intval', $validated['schedule_days'] ?? [])));
        sort($days);
        $validated['schedule_days'] = empty($days) ? null : $days;

        // pinned_targets hanya relevan untuk kategori Menu Tetap.
        // Kosong = tampil di semua kategori (perilaku lama).
        if ($validated['is_pinned']) {
            $targets = array_values(array_unique(array_map('intval', $validated['pinned_targets'] ?? [])));
            $validated['pinned_targets'] = empty($targets) ? null : $targets;
        } else {
            $validated['pinned_targets'] = null;
        }

        return $validated;
    }

    public function create() { return view('tenant.categories.index'); }
    public function show(Category $category) { return back(); }
    public function edit(Category $category) { return back(); }
}
