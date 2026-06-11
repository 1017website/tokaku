<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (!app()->has('currentTenant')) {
            abort(403, 'Akses tidak valid. Gunakan subdomain toko Anda.');
        }

        $tenant = app('currentTenant');
        $tenantId = $tenant->id;

        $todayRevenue = Transaction::notCancelled()->today()->sum('total');
        $todayCount = Transaction::notCancelled()->today()->count();
        $monthRevenue = Transaction::notCancelled()->thisMonth()->sum('total');
        $monthCount = Transaction::notCancelled()->thisMonth()->count();

        $profitQuery = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->leftJoin('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.status', '!=', 'cancelled');

        $todayGrossProfit = (clone $profitQuery)
            ->whereDate('transactions.created_at', today())
            ->sum(DB::raw('(transaction_items.unit_price - COALESCE(products.cost_price,0)) * transaction_items.quantity'));
        $monthGrossProfit = (clone $profitQuery)
            ->whereMonth('transactions.created_at', now()->month)
            ->whereYear('transactions.created_at', now()->year)
            ->sum(DB::raw('(transaction_items.unit_price - COALESCE(products.cost_price,0)) * transaction_items.quantity'));

        $todayExpenses = Expense::whereDate('expense_date', today())->sum('amount');
        $monthExpenses = Expense::whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount');
        $todayNetProfit = $todayGrossProfit - $todayExpenses;
        $monthNetProfit = $monthGrossProfit - $monthExpenses;

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

        $allGrossProfit = (clone $profitQuery)->sum(DB::raw('(transaction_items.unit_price - COALESCE(products.cost_price,0)) * transaction_items.quantity'));
        $allExpenses = Expense::sum('amount');
        $totalNetProfit = $allGrossProfit - $allExpenses;
        $initialCapital = (float) ($tenant->initial_capital ?? 0);
        $capitalProgress = $initialCapital > 0 ? min(100, round(($totalNetProfit / $initialCapital) * 100, 1)) : 0;

        $lowStockProducts = Product::active()->lowStock()->with('category')->orderBy('stock')->limit(5)->get();
        $recentTransactions = Transaction::with('user')->orderByDesc('created_at')->limit(5)->get();

        return view('tenant.dashboard.index', compact(
            'tenant','todayRevenue','todayCount','monthRevenue','monthCount','todayGrossProfit','monthGrossProfit','todayNetProfit','monthNetProfit','monthExpenses','weeklyData','initialCapital','totalNetProfit','capitalProgress','lowStockProducts','recentTransactions'
        ));
    }
}
