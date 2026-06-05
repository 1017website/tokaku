<?php
namespace App\Http\Controllers\Tenant;
use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller {
    public function index() {
        $tenantId = app('currentTenant')->id;
        $activeShift = Shift::where('tenant_id', $tenantId)
            ->where('user_id', auth()->id())->whereNull('closed_at')->latest()->first();

        // Rincian kas untuk shift aktif, dipakai di modal tutup shift supaya
        // kasir tahu berapa yang seharusnya ada di laci (hanya tunai), dan
        // melihat pemasukan non-tunai (QRIS/transfer) secara terpisah.
        $cashSummary = null;
        if ($activeShift) {
            $cashSales    = Transaction::where('shift_id', $activeShift->id)->where('payment_method', 'cash')->sum('total');
            $nonCashSales = Transaction::where('shift_id', $activeShift->id)->where('payment_method', '!=', 'cash')->sum('total');
            $cashSummary = [
                'opening'  => (int) $activeShift->opening_cash,
                'cash'     => (int) $cashSales,
                'noncash'  => (int) $nonCashSales,
                'expected' => (int) ($activeShift->opening_cash + $cashSales),
            ];
        }

        // whereHas memastikan user pemilik shift masih milik tenant ini,
        // sehingga shift "nyasar" (user_id menunjuk user tenant lain karena
        // sisa data / ID daur ulang) tidak ikut tampil.
        // Hitung jumlah & total transaksi secara live, supaya shift yang
        // masih berjalan tidak menampilkan 0 (kolom total_* hanya terisi
        // saat shift ditutup). withCount/withSum menghindari query N+1.
        $shifts = Shift::with('user')
            ->withCount('transactions')
            ->withSum('transactions', 'total')
            ->where('tenant_id', $tenantId)
            ->whereHas('user', fn($q) => $q->where('tenant_id', $tenantId))
            ->latest()->paginate(20);
        return view('tenant.shift.index', compact('activeShift','shifts','cashSummary'));
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
        // Shift sudah otomatis terfilter tenant via global scope BelongsToTenant.
        // Pemilik shift boleh menutup; owner/admin boleh menutup shift kasir manapun di tokonya.
        abort_if($shift->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403, 'Anda tidak berhak menutup shift ini.');
        if (!is_null($shift->closed_at)) {
            return redirect()->route('tenant.shift.index')->withErrors(['Shift ini sudah ditutup.']);
        }
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
        // Shift sudah otomatis terfilter tenant via global scope BelongsToTenant.
        // Jika shift bukan milik tenant ini, route-model-binding mengembalikan 404,
        // sehingga tidak perlu cek tenant manual di sini.
        $transactions = $shift->transactions()->with(['user','items'])->latest()->get();
        return view('tenant.shift.detail', compact('shift','transactions'));
    }
}
