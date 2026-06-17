<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Product;
use App\Models\RawMaterialLog;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!app()->has('currentTenant')) {
            abort(403, 'Akses tidak valid. Gunakan subdomain toko Anda.');
        }

        $tenant = app('currentTenant');
        $tenantId = $tenant->id;

        // Tanggal terpilih untuk ringkasan harian. Default: hari ini.
        // Divalidasi agar tidak melebihi hari ini & tidak salah format.
        try {
            $selectedDate = $request->filled('date')
                ? \Carbon\Carbon::parse($request->date)->startOfDay()
                : today();
        } catch (\Exception $e) {
            $selectedDate = today();
        }
        if ($selectedDate->isFuture()) {
            $selectedDate = today();
        }
        $selectedDateStr = $selectedDate->toDateString();
        $isToday = $selectedDate->isToday();

        // Ringkasan tanggal terpilih (menggantikan "hari ini" yang statis).
        $dayRevenue = Transaction::notCancelled()->whereDate('created_at', $selectedDateStr)->sum('total');
        $dayCount   = Transaction::notCancelled()->whereDate('created_at', $selectedDateStr)->count();
        $monthRevenue = Transaction::notCancelled()->thisMonth()->sum('total');
        $monthCount = Transaction::notCancelled()->thisMonth()->count();

        $profitQuery = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->leftJoin('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.status', '!=', 'cancelled');

        $dayGrossProfit = (clone $profitQuery)
            ->whereDate('transactions.created_at', $selectedDateStr)
            ->sum(DB::raw('(transaction_items.unit_price - COALESCE(products.cost_price,0)) * transaction_items.quantity'));
        $monthGrossProfit = (clone $profitQuery)
            ->whereMonth('transactions.created_at', now()->month)
            ->whereYear('transactions.created_at', now()->year)
            ->sum(DB::raw('(transaction_items.unit_price - COALESCE(products.cost_price,0)) * transaction_items.quantity'));

        $dayExpenses = Expense::whereDate('expense_date', $selectedDateStr)->sum('amount');
        $monthExpenses = Expense::whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount');
        $dayNetProfit = $dayGrossProfit - $dayExpenses;
        $monthNetProfit = $monthGrossProfit - $monthExpenses;

        // Rincian metode bayar pada tanggal terpilih (ringkas, bukan detail transaksi).
        $dayByPayment = Transaction::notCancelled()
            ->whereDate('created_at', $selectedDateStr)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')->get();

        $weeklyData = collect(range(6,0))->map(function ($days) use ($tenantId) {
            $date = now()->subDays($days)->toDateString();
            $revenue = Transaction::notCancelled()->whereDate('created_at', $date)->sum('total');
            $grossProfit = TransactionItem::query()
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->leftJoin('products', 'transaction_items.product_id', '=', 'products.id')
                ->where('transactions.tenant_id', $tenantId)
                ->where('transactions.status', '!=', 'cancelled')
                ->whereDate('transactions.created_at', $date)
                ->sum(DB::raw('(transaction_items.unit_price - COALESCE(products.cost_price,0)) * transaction_items.quantity'));
            $expense = Expense::whereDate('expense_date', $date)->sum('amount');
            return [
                'label' => \Carbon\Carbon::parse($date)->translatedFormat('D'),
                'date' => $date,
                'revenue' => (float) $revenue,
                'profit' => (float) ($grossProfit - $expense),
            ];
        });

        // Sebaran omzet per jam pada tanggal terpilih (untuk grafik harian).
        $hourlyRaw = Transaction::notCancelled()
            ->whereDate('created_at', $selectedDateStr)
            ->selectRaw('HOUR(created_at) as h, SUM(total) as total, COUNT(*) as count')
            ->groupBy('h')->pluck('total', 'h');
        $hourlyData = collect(range(0, 23))->map(fn($h) => [
            'label'   => sprintf('%02d:00', $h),
            'revenue' => (float) ($hourlyRaw[$h] ?? 0),
        ]);

        $allGrossProfit = (clone $profitQuery)->sum(DB::raw('(transaction_items.unit_price - COALESCE(products.cost_price,0)) * transaction_items.quantity'));
        $allExpenses = Expense::sum('amount');
        $totalNetProfit = $allGrossProfit - $allExpenses;
        $initialCapital = (float) ($tenant->initial_capital ?? 0);
        $capitalProgress = $initialCapital > 0 ? min(100, round(($totalNetProfit / $initialCapital) * 100, 1)) : 0;

        $lowStockProducts = Product::active()->lowStock()->with('category')->orderBy('stock')->limit(5)->get();
        $recentTransactions = Transaction::with('user')->notCancelled()->orderByDesc('created_at')->limit(5)->get();

        // Ringkasan pergerakan bahan baku (gudang) hari ini — qty & nilai (rupiah).
        $rmToday = RawMaterialLog::query()
            ->whereDate('created_at', today())
            ->selectRaw("
                COALESCE(SUM(CASE WHEN qty_change < 0 THEN -qty_change ELSE 0 END), 0) AS qty_out,
                COALESCE(SUM(CASE WHEN qty_change > 0 THEN qty_change ELSE 0 END), 0) AS qty_in,
                COALESCE(SUM(CASE WHEN qty_change < 0 THEN -qty_change * price ELSE 0 END), 0) AS value_out,
                COALESCE(SUM(CASE WHEN qty_change > 0 THEN qty_change * price ELSE 0 END), 0) AS value_in
            ")
            ->first();

        $rawMaterialSummary = [
            'qty_out'   => (int) ($rmToday->qty_out ?? 0),
            'qty_in'    => (int) ($rmToday->qty_in ?? 0),
            'value_out' => (float) ($rmToday->value_out ?? 0),
            'value_in'  => (float) ($rmToday->value_in ?? 0),
        ];

        return view('tenant.dashboard.index', compact(
            'tenant','selectedDateStr','isToday','dayRevenue','dayCount','dayGrossProfit','dayNetProfit','dayExpenses','dayByPayment','hourlyData',
            'monthRevenue','monthCount','monthGrossProfit','monthNetProfit','monthExpenses','weeklyData','initialCapital','totalNetProfit','capitalProgress','lowStockProducts','recentTransactions','rawMaterialSummary'
        ));
    }
}
