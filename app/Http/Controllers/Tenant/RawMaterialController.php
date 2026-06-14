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

        // Summary pergerakan HARI INI: qty & nilai (rupiah) masuk vs keluar.
        $today = RawMaterialLog::query()
            ->whereDate('created_at', today())
            ->selectRaw("
                COALESCE(SUM(CASE WHEN qty_change > 0 THEN qty_change ELSE 0 END), 0) AS qty_in,
                COALESCE(SUM(CASE WHEN qty_change < 0 THEN -qty_change ELSE 0 END), 0) AS qty_out,
                COALESCE(SUM(CASE WHEN qty_change > 0 THEN qty_change * price ELSE 0 END), 0) AS value_in,
                COALESCE(SUM(CASE WHEN qty_change < 0 THEN -qty_change * price ELSE 0 END), 0) AS value_out
            ")
            ->first();

        $summary = [
            'qty_in'    => (int) ($today->qty_in ?? 0),
            'qty_out'   => (int) ($today->qty_out ?? 0),
            'value_in'  => (float) ($today->value_in ?? 0),
            'value_out' => (float) ($today->value_out ?? 0),
        ];

        return view('tenant.bahan.index', compact('materials', 'totalItems', 'lowStock', 'units', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'unit'            => 'required|string|max:30',
            'price'           => 'nullable|numeric|min:0',
            'stock'           => 'required|integer|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'note'            => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $material = RawMaterial::create([
                'name'            => $validated['name'],
                'unit'            => $validated['unit'],
                'price'           => $validated['price'] ?? 0,
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
                    'price'           => $material->price,
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
            'price'           => 'nullable|numeric|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'note'            => 'nullable|string|max:255',
            'is_active'       => 'nullable|boolean',
        ]);

        // Catatan: stok TIDAK diubah di sini — pakai endpoint adjust agar tercatat di riwayat.
        $bahan->update([
            'name'            => $validated['name'],
            'unit'            => $validated['unit'],
            'price'           => $validated['price'] ?? 0,
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
            'type'  => 'required|in:in,out,adjustment',
            'qty'   => 'required|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'note'  => 'nullable|string|max:255',
        ]);

        // Untuk in/out, qty minimal 1 (0 tidak bermakna).
        if (in_array($validated['type'], ['in', 'out'], true) && $validated['qty'] < 1) {
            return back()->withErrors(['qty' => 'Jumlah minimal 1.'])->withInput();
        }

        DB::transaction(function () use ($validated, $request, $bahan) {
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

            // Harga satuan untuk log: pakai input bila ada, jika tidak ambil harga master.
            $price = $request->filled('price') ? (float) $validated['price'] : (float) $material->price;

            $updates = ['stock' => $after];
            // Stok masuk dengan harga baru → perbarui harga acuan master.
            if ($validated['type'] === 'in' && $request->filled('price')) {
                $updates['price'] = $price;
            }
            $material->update($updates);

            RawMaterialLog::create([
                'raw_material_id' => $material->id,
                'user_id'         => auth()->id(),
                'qty_before'      => $before,
                'qty_change'      => $change,
                'qty_after'       => $after,
                'price'           => $price,
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
