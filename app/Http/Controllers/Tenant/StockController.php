<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    // Halaman manajemen stok semua produk
    public function index()
    {
        $products = Product::with('category')
            ->orderBy('stock')
            ->paginate(20);

        return view('tenant.stok.index', compact('products'));
    }

    // Tambah/kurangi stok satu produk (via modal/form inline)
    public function update(Request $request, Product $product)
    {
        abort_if($product->tenant_id !== app('currentTenant')->id, 403);

        $request->validate([
            'type'       => 'required|in:restock,adjustment,correction',
            'qty_change' => 'required|integer|not_in:0',
            'note'       => 'nullable|string|max:255',
        ], [
            'qty_change.not_in' => 'Jumlah perubahan tidak boleh 0.',
        ]);

        $qtyChange = (int) $request->qty_change;
        $qtyBefore = $product->stock;
        $qtyAfter  = $qtyBefore + $qtyChange;

        if ($qtyAfter < 0) {
            return back()->withErrors(['qty_change' => 'Stok tidak boleh kurang dari 0. Stok saat ini: ' . $qtyBefore]);
        }

        DB::transaction(function () use ($product, $qtyBefore, $qtyChange, $qtyAfter, $request) {
            $product->update(['stock' => $qtyAfter]);

            StockLog::create([
                'product_id' => $product->id,
                'user_id'    => auth()->id(),
                'qty_before' => $qtyBefore,
                'qty_change' => $qtyChange,
                'qty_after'  => $qtyAfter,
                'type'       => $request->type,
                'note'       => $request->note,
            ]);
        });

        $label = $qtyChange > 0 ? '+' . $qtyChange : $qtyChange;

        return back()->with('success', "Stok {$product->name} berhasil diubah ({$label}). Stok sekarang: {$qtyAfter}");
    }

    // Riwayat stok satu produk
    public function history(Product $product)
    {
        abort_if($product->tenant_id !== app('currentTenant')->id, 403);

        $logs = StockLog::with('user')
            ->where('product_id', $product->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('tenant.stok.history', compact('product', 'logs'));
    }

    // Semua riwayat stok semua produk
    public function allHistory(Request $request)
    {
        $logs = StockLog::with(['product', 'user'])
            ->whereHas('product', fn($q) => $q->where('tenant_id', app('currentTenant')->id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('tenant.stok.all-history', compact('logs'));
    }
}
