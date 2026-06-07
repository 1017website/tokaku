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
    public function index(Request $request)
    {
        $status = $request->query('status', 'active'); // active | inactive | all

        $products = Product::with('category')
            ->when($status === 'active',   fn($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->get();

        $categories = Category::whereHas('products')
            ->orderBy('name')
            ->get();

        return view('tenant.products.index', compact('products', 'categories', 'status'));
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
            'photo'           => 'nullable|image|max:500',
        ], [
            'photo.max'   => 'Ukuran gambar maksimal 500KB.',
            'photo.image' => 'File harus berupa gambar.',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')
                ->store('products', 'public');
        }

        Product::create($validated);

        return redirect()
            ->route('tenant.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        abort_if($product->tenant_id != app('currentTenant')->id, 403);

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
        abort_if($product->tenant_id != app('currentTenant')->id, 403);
        $categories = Category::orderBy('name')->get();

        return view('tenant.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        abort_if($product->tenant_id != app('currentTenant')->id, 403);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'sku'             => 'nullable|string|max:50',
            'category_id'     => 'nullable|exists:categories,id',
            'price'           => 'required|numeric|min:0',
            'cost_price'      => 'nullable|numeric|min:0',
            'stock'           => 'required|integer|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'photo'           => 'nullable|image|max:500',
            'is_active'       => 'boolean',
        ], [
            'photo.max'   => 'Ukuran gambar maksimal 500KB.',
            'photo.image' => 'File harus berupa gambar.',
        ]);

        if ($request->hasFile('photo')) {
            if ($product->photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')
                ->store('products', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');
        $product->update($validated);

        return redirect()
            ->route('tenant.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        abort_if($product->tenant_id != app('currentTenant')->id, 403);
        $product->update(['is_active' => false]);

        return redirect()
            ->route('tenant.products.index')
            ->with('success', 'Produk berhasil dinonaktifkan.');
    }

    public function activate(Product $product)
    {
        abort_if($product->tenant_id != app('currentTenant')->id, 403);
        $product->update(['is_active' => true]);

        return redirect()
            ->route('tenant.products.index', ['status' => 'inactive'])
            ->with('success', 'Produk berhasil diaktifkan kembali.');
    }
}
