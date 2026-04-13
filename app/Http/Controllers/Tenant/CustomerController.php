<?php
namespace App\Http\Controllers\Tenant;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller {
    public function index() {
        $customers = Customer::withCount('transactions')
            ->orderByDesc('total_spent')->paginate(20);
        return view('tenant.pelanggan.index', compact('customers'));
    }

    public function store(Request $request) {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'birthday'=> 'nullable|date',
            'notes'   => 'nullable|string',
        ]);
        Customer::create(array_merge($request->only('name','phone','email','address','birthday','notes'), [
            'tenant_id' => app('currentTenant')->id,
        ]));
        return back()->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer) {
        abort_if($customer->tenant_id !== app('currentTenant')->id, 403);
        $transactions = $customer->transactions()->with('items')->latest()->paginate(15);
        $totalDebt    = $customer->totalDebt();
        return view('tenant.pelanggan.show', compact('customer','transactions','totalDebt'));
    }

    public function update(Request $request, Customer $customer) {
        abort_if($customer->tenant_id !== app('currentTenant')->id, 403);
        $request->validate(['name'=>'required|string|max:255','phone'=>'nullable|string|max:20','email'=>'nullable|email','birthday'=>'nullable|date']);
        $customer->update($request->only('name','phone','email','address','birthday','notes'));
        return back()->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    // API — search pelanggan untuk kasir
    public function search(Request $request) {
        $q = $request->q;
        $customers = Customer::where('tenant_id', app('currentTenant')->id)
            ->where('is_active', true)
            ->where(fn($q2) => $q2->where('name','like',"%$q%")->orWhere('phone','like',"%$q%"))
            ->limit(8)->get(['id','name','phone','points','total_spent']);
        return response()->json($customers);
    }
}
