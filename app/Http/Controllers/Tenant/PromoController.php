<?php
namespace App\Http\Controllers\Tenant;
use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\Product;
use Illuminate\Http\Request;

class PromoController extends Controller {
    public function index() {
        $promos = Promo::with('product')->latest()->paginate(15);
        $products = Product::active()->orderBy('name')->get();
        return view('tenant.promo.index', compact('promos','products'));
    }

    public function store(Request $request) {
        $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'nullable|string|max:50',
            'type'            => 'required|in:percent,fixed,buyxgety',
            'value'           => 'required_if:type,percent,fixed|nullable|numeric|min:0',
            'min_qty'         => 'required_if:type,buyxgety|nullable|integer|min:1',
            'free_qty'        => 'required_if:type,buyxgety|nullable|integer|min:1',
            'min_transaction' => 'nullable|integer|min:0',
            'max_discount'    => 'nullable|integer|min:0',
            'product_id'      => 'nullable|exists:products,id',
            'starts_at'       => 'nullable|date',
            'ends_at'         => 'nullable|date|after_or_equal:starts_at',
        ], [
            'value.required_if'    => 'Nilai diskon wajib diisi.',
            'min_qty.required_if'  => 'Jumlah beli (qty) wajib diisi.',
            'free_qty.required_if' => 'Jumlah gratis (qty) wajib diisi.',
        ]);
        $data = $request->only('name','code','type','value','min_qty','free_qty','min_transaction','max_discount','product_id','starts_at','ends_at');

        // Kolom NOT NULL di DB — pastikan tidak null walau input kosong
        // (ConvertEmptyStringsToNull mengubah input kosong jadi null).
        $data['value']    = $data['value'] ?? 0;
        $data['min_qty']  = $data['min_qty'] ?? 1;
        $data['free_qty'] = $data['free_qty'] ?? 0;

        Promo::create(array_merge($data, [
            'tenant_id' => app('currentTenant')->id,
        ]));
        return back()->with('success', 'Promo berhasil dibuat.');
    }

    public function toggle(Promo $promo) {
        abort_if((int) $promo->tenant_id !== (int) app('currentTenant')->id, 403);
        $promo->update(['is_active' => !$promo->is_active]);
        return back()->with('success', 'Status promo diperbarui.');
    }

    public function destroy(Promo $promo) {
        abort_if((int) $promo->tenant_id !== (int) app('currentTenant')->id, 403);
        $promo->delete();
        return back()->with('success', 'Promo dihapus.');
    }

    // API — hitung promo OTOMATIS untuk kasir berdasarkan isi keranjang.
    // Menerima items: [{id, qty, price}], mengembalikan promo terbaik.
    public function calculate(Request $request, \App\Services\PromoService $promoService) {
        $tenantId = app('currentTenant')->id;
        $items = $request->input('items', []);

        if (empty($items) || !is_array($items)) {
            return response()->json(['discount' => 0, 'promo_id' => null, 'message' => 'Keranjang kosong.']);
        }

        $best = $promoService->bestPromo($items, $tenantId);

        if (!$best['promo_id']) {
            return response()->json(['discount' => 0, 'promo_id' => null, 'message' => 'Tidak ada promo yang berlaku.']);
        }

        return response()->json([
            'discount'   => $best['discount'],
            'promo_id'   => $best['promo_id'],
            'promo_name' => $best['promo_name'],
            'label'      => $best['label'],
            'message'    => "Promo \"{$best['promo_name']}\" diterapkan.",
        ]);
    }
}
