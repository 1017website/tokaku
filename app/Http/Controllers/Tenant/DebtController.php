<?php
namespace App\Http\Controllers\Tenant;
use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\DebtPayment;
use Illuminate\Http\Request;

class DebtController extends Controller {
    public function index() {
        $debts     = Debt::with('customer')->where('tenant_id', app('currentTenant')->id)
            ->whereIn('status',['unpaid','partial'])->latest()->paginate(20);
        $totalDebt = Debt::where('tenant_id', app('currentTenant')->id)
            ->whereIn('status',['unpaid','partial'])->sum(\DB::raw('amount - paid_amount'));
        $paidToday = DebtPayment::whereHas('debt', fn($q)=>$q->where('tenant_id', app('currentTenant')->id))
            ->whereDate('created_at', today())->sum('amount');
        return view('tenant.hutang.index', compact('debts','totalDebt','paidToday'));
    }

    public function history() {
        $debts = Debt::with('customer')->where('tenant_id', app('currentTenant')->id)
            ->where('status','paid')->latest()->paginate(20);
        return view('tenant.hutang.history', compact('debts'));
    }

    public function store(Request $request) {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'amount'         => 'required|integer|min:1',
            'due_date'       => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);
        Debt::create(array_merge($request->only('customer_name','customer_phone','amount','due_date','notes','customer_id'), [
            'tenant_id'   => app('currentTenant')->id,
            'paid_amount' => 0,
            'status'      => 'unpaid',
        ]));
        return back()->with('success', 'Hutang berhasil dicatat.');
    }

    public function pay(Request $request, Debt $debt) {
        abort_if($debt->tenant_id !== app('currentTenant')->id, 403);
        $request->validate([
            'amount' => 'required|integer|min:1|max:'.$debt->remaining,
            'note'   => 'nullable|string',
        ]);

        DebtPayment::create(['debt_id'=>$debt->id,'user_id'=>auth()->id(),'amount'=>$request->amount,'note'=>$request->note]);

        $newPaid = $debt->paid_amount + $request->amount;
        $status  = $newPaid >= $debt->amount ? 'paid' : 'partial';
        $debt->update(['paid_amount'=>$newPaid,'status'=>$status]);

        return back()->with('success', 'Pembayaran hutang berhasil dicatat.');
    }
}
