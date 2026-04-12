<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function show(Product $product)
    {
        abort_if($product->tenant_id !== app('currentTenant')->id, 403);

        // Riwayat transaksi produk ini
        $history = TransactionItem::with(['transaction', 'transaction.user'])
            ->where('product_id', $product->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        // Statistik produk
        $totalTerjual = TransactionItem::where('product_id', $product->id)->sum('quantity');
        $totalOmzet   = TransactionItem::where('product_id', $product->id)->sum('subtotal');
        $totalLaba    = $product->cost_price > 0
            ? TransactionItem::where('product_id', $product->id)
                ->sum(DB::raw("(unit_price - {$product->cost_price}) * quantity"))
            : null;

        return view('tenant.products.show', compact(
            'product', 'history', 'totalTerjual', 'totalOmzet', 'totalLaba'
        ));
    }

    public function edit(Product $product)
    {
        abort_if($product->tenant_id !== app('currentTenant')->id, 403);
        $categories = Category::orderBy('name')->get();

        return view('tenant.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        abort_if($product->tenant_id !== app('currentTenant')->id, 403);

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

        $validated['is_active'] = $request->boolean('is_active');
        $product->update($validated);

        return redirect()
            ->route('tenant.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        abort_if($product->tenant_id !== app('currentTenant')->id, 403);
        $product->update(['is_active' => false]);

        return redirect()
            ->route('tenant.products.index')
            ->with('success', 'Produk berhasil dinonaktifkan.');
    }
}
