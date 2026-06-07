<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\RawMaterialLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gudang Bahan Baku — pembukuan stok bahan (owner & admin).
 * Murni catatan stok; tidak terhubung ke produk jual / transaksi kasir.
 */
class RawMaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = RawMaterial::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->status === 'low', fn($q) => $q->whereColumn('stock', '<=', 'low_stock_alert')->where('low_stock_alert', '>', 0));

        $materials = $query->orderBy('name')->paginate(20)->withQueryString();

        $totalItems  = RawMaterial::active()->count();
        $lowStock    = RawMaterial::active()->whereColumn('stock', '<=', 'low_stock_alert')->where('low_stock_alert', '>', 0)->count();
        $units       = RawMaterial::units();

        return view('tenant.bahan.index', compact('materials', 'totalItems', 'lowStock', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'unit'            => 'required|string|max:30',
            'stock'           => 'required|integer|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'note'            => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $material = RawMaterial::create([
                'name'            => $validated['name'],
                'unit'            => $validated['unit'],
                'stock'           => $validated['stock'],
                'low_stock_alert' => $validated['low_stock_alert'] ?? 0,
                'note'            => $validated['note'] ?? null,
            ]);

            // Catat stok awal sebagai log "masuk" bila > 0.
            if ($material->stock > 0) {
                RawMaterialLog::create([
                    'raw_material_id' => $material->id,
                    'user_id'         => auth()->id(),
                    'qty_before'      => 0,
                    'qty_change'      => $material->stock,
                    'qty_after'       => $material->stock,
                    'type'            => 'in',
                    'note'            => 'Stok awal',
                ]);
            }
        });

        return back()->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function update(Request $request, RawMaterial $bahan)
    {
        abort_if($bahan->tenant_id != app('currentTenant')->id, 403);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'unit'            => 'required|string|max:30',
            'low_stock_alert' => 'nullable|integer|min:0',
            'note'            => 'nullable|string|max:255',
            'is_active'       => 'nullable|boolean',
        ]);

        // Catatan: stok TIDAK diubah di sini — pakai endpoint adjust agar tercatat di riwayat.
        $bahan->update([
            'name'            => $validated['name'],
            'unit'            => $validated['unit'],
            'low_stock_alert' => $validated['low_stock_alert'] ?? 0,
            'note'            => $validated['note'] ?? null,
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Data bahan baku berhasil diperbarui.');
    }

    /**
     * Catat pergerakan stok: masuk / keluar / penyesuaian.
     * Contoh: pentol keju stok 100, keluar 20 -> sisa 80.
     */
    public function adjust(Request $request, RawMaterial $bahan)
    {
        abort_if($bahan->tenant_id != app('currentTenant')->id, 403);

        $validated = $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'qty'  => 'required|integer|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        // Untuk in/out, qty minimal 1 (0 tidak bermakna).
        if (in_array($validated['type'], ['in', 'out'], true) && $validated['qty'] < 1) {
            return back()->withErrors(['qty' => 'Jumlah minimal 1.'])->withInput();
        }

        DB::transaction(function () use ($validated, $bahan) {
            $material = RawMaterial::where('id', $bahan->id)->lockForUpdate()->first();

            $before = $material->stock;
            $qty    = (int) $validated['qty'];

            $change = match ($validated['type']) {
                'in'         => $qty,
                'out'        => -$qty,
                'adjustment' => $qty - $before, // qty = stok hasil hitung ulang
            };

            $after = $before + $change;
            if ($after < 0) {
                throw new \Exception('Stok tidak boleh kurang dari 0.');
            }

            $material->update(['stock' => $after]);

            RawMaterialLog::create([
                'raw_material_id' => $material->id,
                'user_id'         => auth()->id(),
                'qty_before'      => $before,
                'qty_change'      => $change,
                'qty_after'       => $after,
                'type'            => $validated['type'],
                'note'            => $validated['note'] ?? null,
            ]);
        });

        return back()->with('success', 'Stok bahan baku berhasil dicatat.');
    }

    public function history(RawMaterial $bahan)
    {
        abort_if($bahan->tenant_id != app('currentTenant')->id, 403);

        $logs = $bahan->logs()->with('user')->orderByDesc('id')->paginate(30);

        return view('tenant.bahan.history', compact('bahan', 'logs'));
    }

    public function destroy(RawMaterial $bahan)
    {
        abort_if($bahan->tenant_id != app('currentTenant')->id, 403);
        $bahan->delete();

        return back()->with('success', 'Bahan baku berhasil dihapus.');
    }
}
