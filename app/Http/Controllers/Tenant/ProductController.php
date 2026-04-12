<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->orderBy('name')
            ->paginate(15);

        return view('tenant.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('tenant.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'sku'             => 'nullable|string|max:50',
            'category_id'     => 'nullable|exists:categories,id',
            'price'           => 'required|numeric|min:0',
            'cost_price'      => 'nullable|numeric|min:0',
            'stock'           => 'required|integer|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'photo'           => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')
                ->store('products', 'local');
        }

        Product::create($validated);

        return redirect()
            ->route('tenant.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('tenant.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'sku'             => 'nullable|string|max:50',
            'category_id'     => 'nullable|exists:categories,id',
            'price'           => 'required|numeric|min:0',
            'cost_price'      => 'nullable|numeric|min:0',
            'stock'           => 'required|integer|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'photo'           => 'nullable|image|max:2048',
            'is_active'       => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')
                ->store('products', 'local');
        }

        $product->update($validated);

        return redirect()
            ->route('tenant.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);

        return redirect()
            ->route('tenant.products.index')
            ->with('success', 'Produk berhasil dinonaktifkan.');
    }

    public function show(Product $product)
    {
        return view('tenant.products.show', compact('product'));
    }
}
