<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('tenant.categories.index', compact('categories'));
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

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki produk.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Validasi + normalisasi. Masa berlaku hanya untuk promo/bundling;
     * untuk regular kita kosongkan agar konsisten.
     */
    protected function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'type'      => 'required|in:regular,promo,bundling',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
        ], [
            'ends_at.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
        ]);

        if ($validated['type'] === 'regular') {
            $validated['starts_at'] = null;
            $validated['ends_at'] = null;
        }

        return $validated;
    }

    public function create() { return view('tenant.categories.index'); }
    public function show(Category $category) { return back(); }
    public function edit(Category $category) { return back(); }
}
