<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        // Guard: kalau tidak ada tenant, redirect ke login
        if (!app()->has('currentTenant')) {
            abort(403, 'Akses tidak valid. Gunakan subdomain toko Anda.');
        }

        $tenant = app('currentTenant');

        // Ringkasan hari ini
        $todayRevenue = Transaction::today()->sum('total');
        $todayCount   = Transaction::today()->count();

        // Ringkasan bulan ini
        $monthRevenue = Transaction::thisMonth()->sum('total');
        $monthCount   = Transaction::thisMonth()->count();

        // Produk stok menipis
        $lowStockProducts = Product::active()
            ->lowStock()
            ->with('category')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        // Transaksi terbaru
        $recentTransactions = Transaction::with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('tenant.dashboard.index', compact(
            'tenant',
            'todayRevenue',
            'todayCount',
            'monthRevenue',
            'monthCount',
            'lowStockProducts',
            'recentTransactions',
        ));
    }
}
