<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;

class SuperAdminController extends Controller
{
    // Dashboard utama
    public function index()
    {
        $totalTenants     = Tenant::count();
        $activeTenants    = Tenant::where('status', 'active')->count();
        $trialTenants     = Tenant::where('status', 'trial')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();
        $pendingTenants   = Tenant::where('status', 'pending')->count();
        $waitingPayments  = \App\Models\PaymentInvoice::where('status', 'waiting_confirmation')->count();

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
            'pendingTenants', 'waitingPayments',
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

        // Riwayat pembayaran langganan
        $invoices = \App\Models\PaymentInvoice::with('plan')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->get();
        $paidCount = $invoices->where('status', 'paid')->count();
        $totalPaid = $invoices->where('status', 'paid')->sum('total_amount');

        return view('superadmin.tenants.detail', compact(
            'tenant', 'users', 'recentTransactions', 'monthlyRevenue', 'topProducts',
            'invoices', 'paidCount', 'totalPaid'
        ));
    }

    // Buat tenant baru
    public function storeTenant(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'subdomain'  => 'required|string|max:50|unique:tenants,subdomain|alpha_dash',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:8',
            'status'     => 'required|in:trial,active',
            'trial_days' => 'nullable|integer|min:1|max:365',
        ]);

        $isTrial = $request->status === 'trial';
        $trialDays = (int) ($request->input('trial_days') ?: config('tokaku.trial_days', 14));

        $tenant = Tenant::create([
            'name'          => $request->name,
            'subdomain'     => $request->subdomain,
            'phone'         => $request->phone,
            'status'        => $isTrial ? 'trial' : 'active',
            'trial_ends_at' => $isTrial ? now()->addDays($trialDays) : null,
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Owner ' . $tenant->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $msg = $isTrial
            ? "Tenant {$tenant->name} berhasil dibuat. Trial {$trialDays} hari dimulai."
            : "Tenant {$tenant->name} berhasil dibuat dengan status aktif.";

        return back()->with('success', $msg);
    }

    // Suspend / aktifkan tenant
    public function suspend(Tenant $tenant)
    {
        $status = $tenant->status === 'suspended' ? 'active' : 'suspended';
        $tenant->update(['status' => $status]);

        $label = $status === 'suspended' ? 'ditangguhkan' : 'diaktifkan kembali';

        return back()->with('success', "Tenant {$tenant->name} berhasil {$label}.");
    }

    /**
     * Setujui pendaftaran tenant pending → mulai trial.
     */
    public function approve(Request $request, Tenant $tenant)
    {
        abort_if($tenant->status !== 'pending', 422, 'Tenant ini tidak dalam status menunggu persetujuan.');

        $trialDays = (int) ($request->input('trial_days') ?: config('tokaku.trial_days', 14));

        $tenant->update([
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays($trialDays),
            'approved_at'   => now(),
            'rejected_at'   => null,
            'reject_reason' => null,
        ]);

        return back()->with('success', "Pendaftaran {$tenant->name} disetujui. Trial {$trialDays} hari dimulai.");
    }

    /**
     * Tolak pendaftaran tenant pending.
     */
    public function reject(Request $request, Tenant $tenant)
    {
        abort_if($tenant->status !== 'pending', 422, 'Tenant ini tidak dalam status menunggu persetujuan.');

        $request->validate(['reject_reason' => 'nullable|string|max:255']);

        $tenant->update([
            'status'        => 'rejected',
            'rejected_at'   => now(),
            'reject_reason' => $request->reject_reason,
        ]);

        return back()->with('success', "Pendaftaran {$tenant->name} ditolak.");
    }

    /**
     * Daftar invoice pembayaran (default: menunggu konfirmasi).
     */
    public function payments(Request $request)
    {
        $query = \App\Models\PaymentInvoice::with(['tenant', 'plan'])->latest();

        $status = $request->input('status', 'waiting_confirmation');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(20)->appends($request->query());
        $waitingCount = \App\Models\PaymentInvoice::where('status', 'waiting_confirmation')->count();

        return view('superadmin.payments', compact('invoices', 'status', 'waitingCount'));
    }

    /**
     * Verifikasi pembayaran invoice: setujui (perpanjang langganan) atau tolak.
     */
    public function confirmPayment(Request $request, \App\Models\PaymentInvoice $invoice)
    {
        $request->validate(['action' => 'required|in:approve,reject', 'note' => 'nullable|string|max:255']);
        abort_if($invoice->status !== 'waiting_confirmation', 422, 'Invoice ini tidak menunggu konfirmasi.');

        $tenant = $invoice->tenant;

        if ($request->action === 'approve') {
            // Perpanjang dari sisa waktu jika masih aktif, atau dari sekarang.
            $start = ($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture())
                ? $tenant->trial_ends_at
                : now();
            $tenant->update([
                'status'        => 'active',
                'trial_ends_at' => $start->copy()->addMonths($invoice->duration_months),
            ]);
            $invoice->update([
                'status'       => 'paid',
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id(),
                'note'         => $request->note,
            ]);
            return back()->with('success', "Pembayaran {$invoice->invoice_no} dikonfirmasi. Langganan {$tenant->name} aktif {$invoice->duration_months} bulan.");
        }

        $invoice->update([
            'status'       => 'rejected',
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id(),
            'note'         => $request->note,
        ]);
        return back()->with('success', "Pembayaran {$invoice->invoice_no} ditolak.");
    }

    // Perpanjang trial / ubah status
    public function updateStatus(Request $request, Tenant $tenant)
    {
        $request->validate([
            'status'        => 'required|in:trial,active,suspended',
            'trial_ends_at' => 'nullable|date',
            'trial_days'    => 'nullable|integer|min:1|max:365',
        ]);

        $trialEndsAt = $request->filled('trial_days')
            ? now()->addDays((int) $request->trial_days)
            : ($request->filled('trial_ends_at') ? \Carbon\Carbon::parse($request->trial_ends_at)->endOfDay() : null);

        $tenant->update([
            'status'        => $request->status,
            'trial_ends_at' => $trialEndsAt,
        ]);

        return back()->with('success', "Status tenant {$tenant->name} berhasil diperbarui.");
    }

    /**
     * Edit data dasar tenant (nama, telepon, alamat, jenis usaha).
     */
    public function updateTenant(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:500',
            'business_type' => 'nullable|string|max:100',
            'owner_name'    => 'nullable|string|max:255',
        ]);

        $tenant->update($validated);

        return back()->with('success', "Data {$tenant->name} berhasil diperbarui.");
    }

    /**
     * Perpanjang trial: tambah N hari dari sisa trial (atau dari sekarang bila sudah lewat).
     */
    public function extendTrial(Request $request, Tenant $tenant)
    {
        $request->validate(['days' => 'required|integer|min:1|max:365']);

        $base = ($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture())
            ? $tenant->trial_ends_at
            : now();

        $tenant->update([
            'status'        => 'trial',
            'trial_ends_at' => $base->copy()->addDays((int) $request->days),
        ]);

        return back()->with('success', "Trial {$tenant->name} diperpanjang {$request->days} hari.");
    }

    /**
     * Hentikan trial sekarang juga (set kedaluwarsa ke saat ini).
     */
    public function stopTrial(Tenant $tenant)
    {
        abort_if($tenant->status !== 'trial', 422, 'Tenant ini tidak sedang dalam masa trial.');

        $tenant->update(['trial_ends_at' => now()->subSecond()]);

        return back()->with('success', "Trial {$tenant->name} dihentikan.");
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

    public function settings()
    {
        $keys = [
            'app_name', 'app_logo_path', 'app_logo_full', 'app_favicon',
            'seo_title', 'seo_description', 'seo_keywords', 'seo_og_image',
            'google_ads_id', 'google_analytics_id', 'meta_pixel_id', 'gtm_id',
            'bank_name', 'bank_account_no', 'bank_account_name',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = AppSetting::getValue($key, $key === 'app_name' ? 'Tokaku' : null);
        }

        return view('superadmin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'app_name'            => 'required|string|max:100',
            'app_logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'app_logo_full_file'  => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'app_favicon_file'    => 'nullable|image|mimes:png,ico,svg,jpg,jpeg,webp|max:1024',
            'seo_og_image_file'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            // SEO teks
            'seo_title'           => 'nullable|string|max:70',
            'seo_description'     => 'nullable|string|max:300',
            'seo_keywords'        => 'nullable|string|max:255',
            // Ads / Tracking
            'google_ads_id'       => ['nullable', 'string', 'max:30', 'regex:/^AW-[A-Za-z0-9]+$/'],
            'google_analytics_id' => ['nullable', 'string', 'max:30', 'regex:/^G-[A-Za-z0-9]+$/'],
            'gtm_id'              => ['nullable', 'string', 'max:30', 'regex:/^GTM-[A-Za-z0-9]+$/'],
            'meta_pixel_id'       => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            // Rekening pembayaran
            'bank_name'           => 'nullable|string|max:50',
            'bank_account_no'     => 'nullable|string|max:50',
            'bank_account_name'   => 'nullable|string|max:100',
        ], [
            'google_ads_id.regex'       => 'Format Google Ads ID harus AW-XXXXXXXXX.',
            'google_analytics_id.regex' => 'Format Google Analytics ID harus G-XXXXXXXXXX.',
            'gtm_id.regex'              => 'Format GTM ID harus GTM-XXXXXXX.',
            'meta_pixel_id.regex'       => 'Meta Pixel ID hanya boleh berisi angka.',
        ]);

        AppSetting::setValue('app_name', $validated['app_name']);

        // ── Upload file (logo ikon, logo full, favicon, OG image) ──
        $uploads = [
            'app_logo'           => ['key' => 'app_logo_path',  'dir' => 'app-logos'],
            'app_logo_full_file' => ['key' => 'app_logo_full',  'dir' => 'app-logos'],
            'app_favicon_file'   => ['key' => 'app_favicon',    'dir' => 'app-favicon'],
            'seo_og_image_file'  => ['key' => 'seo_og_image',   'dir' => 'app-seo'],
        ];

        foreach ($uploads as $field => $conf) {
            if ($request->hasFile($field)) {
                $old = AppSetting::getValue($conf['key']);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $path = $request->file($field)->store($conf['dir'], 'public');
                AppSetting::setValue($conf['key'], $path);
            }
        }

        // ── Teks: SEO & Ads ──
        $textKeys = [
            'seo_title', 'seo_description', 'seo_keywords',
            'google_ads_id', 'google_analytics_id', 'meta_pixel_id', 'gtm_id',
            'bank_name', 'bank_account_no', 'bank_account_name',
        ];

        foreach ($textKeys as $key) {
            AppSetting::setValue($key, $request->filled($key) ? trim($request->input($key)) : null);
        }

        return back()->with('success', 'Pengaturan aplikasi berhasil diperbarui.');
    }
    public function maintenance()
    {
        $storageLinked = File::exists(public_path('storage'));

        return view('superadmin.maintenance.index', compact('storageLinked'));
    }

    public function runMigrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            return back()->with('success', 'php artisan migrate berhasil dijalankan.')
                ->with('artisan_output', $output ?: 'Tidak ada output dari command.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menjalankan migrate: ' . $e->getMessage());
        }
    }

    public function runStorageLink()
    {
        try {
            Artisan::call('storage:link', ['--force' => true]);
            $output = trim(Artisan::output());

            return back()->with('success', 'php artisan storage:link berhasil dijalankan.')
                ->with('artisan_output', $output ?: 'Tidak ada output dari command.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menjalankan storage:link: ' . $e->getMessage());
        }
    }

    public function runOptimizeClear()
    {
        try {
            Artisan::call('optimize:clear');
            $output = trim(Artisan::output());

            return back()->with('success', 'php artisan optimize:clear berhasil dijalankan.')
                ->with('artisan_output', $output ?: 'Tidak ada output dari command.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menjalankan optimize:clear: ' . $e->getMessage());
        }
    }

}

