<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    // Dashboard utama
    public function index()
    {
        $totalTenants     = Tenant::count();
        $activeTenants    = Tenant::where('status', 'active')->count();
        $trialTenants     = Tenant::where('status', 'trial')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();

        // Total transaksi & revenue semua tenant
        $totalRevenue      = Transaction::sum('total');
        $totalTransactions = Transaction::count();
        $todayRevenue      = Transaction::whereDate('created_at', today())->sum('total');
        $todayTransactions = Transaction::whereDate('created_at', today())->count();

        // Tenant terbaru
        $recentTenants = Tenant::withCount('users')
            ->latest()
            ->limit(5)
            ->get();

        // Transaksi terbaru semua tenant
        $recentTransactions = Transaction::with(['user', 'user.tenant'])
            ->latest()
            ->limit(8)
            ->get();

        // Revenue per hari (30 hari terakhir) untuk chart
        $dailyRevenue = Transaction::selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('superadmin.dashboard', compact(
            'totalTenants', 'activeTenants', 'trialTenants', 'suspendedTenants',
            'totalRevenue', 'totalTransactions', 'todayRevenue', 'todayTransactions',
            'recentTenants', 'recentTransactions', 'dailyRevenue'
        ));
    }

    // Daftar semua tenant
    public function tenants(Request $request)
    {
        $query = Tenant::withCount(['users', 'transactions'])
            ->withSum('transactions', 'total');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('subdomain', 'like', '%' . $request->search . '%');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tenants = $query->orderByDesc('created_at')->paginate(15);

        return view('superadmin.tenants.index', compact('tenants'));
    }

    // Detail satu tenant
    public function tenantDetail(Tenant $tenant)
    {
        $tenant->loadCount(['users', 'transactions', 'products']);
        $tenant->loadSum('transactions', 'total');

        $users = User::where('tenant_id', $tenant->id)->get();

        $recentTransactions = Transaction::with('user')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->limit(10)
            ->get();

        $monthlyRevenue = Transaction::where('tenant_id', $tenant->id)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total) as total, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        $topProducts = \App\Models\TransactionItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('transaction', fn($q) => $q->where('tenant_id', $tenant->id))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('superadmin.tenants.detail', compact(
            'tenant', 'users', 'recentTransactions', 'monthlyRevenue', 'topProducts'
        ));
    }

    // Buat tenant baru
    public function storeTenant(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'subdomain' => 'required|string|max:50|unique:tenants,subdomain|alpha_dash',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8',
        ]);

        $tenant = Tenant::create([
            'name'          => $request->name,
            'subdomain'     => $request->subdomain,
            'phone'         => $request->phone,
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Owner ' . $tenant->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        return back()->with('success', "Tenant {$tenant->name} berhasil dibuat. Trial 14 hari dimulai.");
    }

    // Suspend / aktifkan tenant
    public function suspend(Tenant $tenant)
    {
        $status = $tenant->status === 'suspended' ? 'active' : 'suspended';
        $tenant->update(['status' => $status]);

        $label = $status === 'suspended' ? 'ditangguhkan' : 'diaktifkan kembali';

        return back()->with('success', "Tenant {$tenant->name} berhasil {$label}.");
    }

    // Perpanjang trial / ubah status
    public function updateStatus(Request $request, Tenant $tenant)
    {
        $request->validate([
            'status'        => 'required|in:trial,active,suspended',
            'trial_ends_at' => 'nullable|date',
        ]);

        $tenant->update([
            'status'        => $request->status,
            'trial_ends_at' => $request->trial_ends_at,
        ]);

        return back()->with('success', "Status tenant {$tenant->name} berhasil diperbarui.");
    }

    // Laporan semua transaksi lintas tenant
    public function laporan(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();
        $tenantId  = $request->tenant_id;

        $query = Transaction::with(['user', 'user.tenant'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $transactions     = (clone $query)->latest()->paginate(20);
        $totalRevenue     = (clone $query)->sum('total');
        $totalCount       = (clone $query)->count();

        // Revenue per tenant
        $revenueByTenant = (clone $query)
            ->select('tenant_id', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('tenant_id')
            ->with('user.tenant')
            ->get()
            ->map(function($t) {
                return [
                    'tenant' => Tenant::find($t->tenant_id),
                    'total'  => $t->total,
                    'count'  => $t->count,
                ];
            });

        $allTenants = Tenant::orderBy('name')->get();

        return view('superadmin.laporan', compact(
            'transactions', 'totalRevenue', 'totalCount',
            'revenueByTenant', 'allTenants',
            'startDate', 'endDate', 'tenantId'
        ));
    }
}
