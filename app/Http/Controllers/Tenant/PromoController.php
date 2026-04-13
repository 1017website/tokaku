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
            'value'           => 'required|numeric|min:0',
            'min_transaction' => 'nullable|integer|min:0',
            'max_discount'    => 'nullable|integer|min:0',
            'product_id'      => 'nullable|exists:products,id',
            'starts_at'       => 'nullable|date',
            'ends_at'         => 'nullable|date|after_or_equal:starts_at',
        ]);
        Promo::create(array_merge($request->only('name','code','type','value','min_qty','free_qty','min_transaction','max_discount','product_id','starts_at','ends_at'), [
            'tenant_id' => app('currentTenant')->id,
        ]));
        return back()->with('success', 'Promo berhasil dibuat.');
    }

    public function toggle(Promo $promo) {
        abort_if($promo->tenant_id !== app('currentTenant')->id, 403);
        $promo->update(['is_active' => !$promo->is_active]);
        return back()->with('success', 'Status promo diperbarui.');
    }

    public function destroy(Promo $promo) {
        abort_if($promo->tenant_id !== app('currentTenant')->id, 403);
        $promo->delete();
        return back()->with('success', 'Promo dihapus.');
    }

    // API — hitung diskon untuk kasir
    public function calculate(Request $request) {
        $tenantId = app('currentTenant')->id;
        $subtotal = (int)$request->subtotal;
        $code     = $request->code;

        $promo = Promo::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->when($code, fn($q) => $q->where('code', $code))
            ->get()
            ->filter(fn($p) => $p->isValid())
            ->first();

        if (!$promo) return response()->json(['discount'=>0,'message'=>'Promo tidak ditemukan atau sudah tidak berlaku.']);

        $discount = $promo->calculateDiscount($subtotal);
        return response()->json(['discount'=>$discount,'promo_id'=>$promo->id,'promo_name'=>$promo->name,'message'=>"Promo \"{$promo->name}\" diterapkan."]);
    }
}
