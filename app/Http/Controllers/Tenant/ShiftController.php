<?php
namespace App\Http\Controllers\Tenant;
use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller {
    public function index() {
        $activeShift = Shift::where('tenant_id', app('currentTenant')->id)
            ->where('user_id', auth()->id())->whereNull('closed_at')->latest()->first();
        $shifts = Shift::with('user')->where('tenant_id', app('currentTenant')->id)
            ->latest()->paginate(20);
        return view('tenant.shift.index', compact('activeShift','shifts'));
    }

    public function open(Request $request) {
        // Cek kalau sudah ada shift aktif
        $existing = Shift::where('tenant_id', app('currentTenant')->id)
            ->where('user_id', auth()->id())->whereNull('closed_at')->first();
        if ($existing) return back()->withErrors(['Anda sudah memiliki shift aktif.']);

        $request->validate(['opening_cash'=>'required|integer|min:0']);

        Shift::create([
            'tenant_id'    => app('currentTenant')->id,
            'user_id'      => auth()->id(),
            'opening_cash' => $request->opening_cash,
            'opened_at'    => now(),
        ]);
        return back()->with('success', 'Shift dibuka. Selamat bekerja!');
    }

    public function close(Request $request, Shift $shift) {
        abort_if($shift->user_id !== auth()->id(), 403);
        $request->validate(['closing_cash'=>'required|integer|min:0','notes'=>'nullable|string']);

        // Hitung semua transaksi tunai dalam shift ini
        $cashRevenue = Transaction::where('shift_id', $shift->id)
            ->where('payment_method','cash')->sum('total');

        $expected  = $shift->opening_cash + $cashRevenue;
        $diff      = $request->closing_cash - $expected;
        $totalTrx  = Transaction::where('shift_id', $shift->id)->count();
        $totalRev  = Transaction::where('shift_id', $shift->id)->sum('total');

        $shift->update([
            'closing_cash'      => $request->closing_cash,
            'expected_cash'     => $expected,
            'cash_difference'   => $diff,
            'total_transactions'=> $totalTrx,
            'total_revenue'     => $totalRev,
            'closed_at'         => now(),
            'notes'             => $request->notes,
        ]);
        return back()->with('success', 'Shift berhasil ditutup.');
    }

    public function show(Shift $shift) {
        abort_if($shift->tenant_id !== app('currentTenant')->id, 403);
        $transactions = $shift->transactions()->with('user')->latest()->get();
        return view('tenant.shift.detail', compact('shift','transactions'));
    }
}
