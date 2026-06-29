<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Tenant\BillingController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\TransactionController;
use App\Http\Controllers\Tenant\ProfileController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\StockController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\PromoController;
use App\Http\Controllers\Tenant\DebtController;
use App\Http\Controllers\Tenant\ShiftController;
use App\Http\Controllers\Tenant\ExpenseController;
use App\Http\Controllers\Tenant\RawMaterialController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\PricingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'superadmin'
            ? redirect()->route('superadmin.dashboard')
            : redirect()->to(auth()->user()->homeRoute());
    }

    $plans = \App\Models\PricingPlan::active()->ordered()->get();

    return view('welcome', compact('plans'));
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/register',  [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// TENANT
Route::middleware(['auth', 'tenant', 'subscription'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('role:owner')->name('tenant.dashboard');

    // Halaman untuk user tanpa akses modul apapun
    Route::get('/no-access', fn() => view('tenant.no-access'))->name('tenant.no-access');
    Route::resource('products', ProductController::class)->names('tenant.products')->middleware('permission:produk');
    Route::put('products/{product}/activate', [ProductController::class, 'activate'])->name('tenant.products.activate')->middleware('permission:produk');
    Route::resource('categories', CategoryController::class)->names('tenant.categories')->middleware('permission:kategori');
    Route::put('categories/{category}/activate', [CategoryController::class, 'activate'])->name('tenant.categories.activate')->middleware('permission:kategori');

    // Kasir
    Route::prefix('kasir')->name('tenant.kasir.')->middleware('permission:kasir')->group(function () {
        Route::get('/',               [TransactionController::class, 'index'])->name('index');
        Route::post('/proses',        [TransactionController::class, 'proses'])->name('proses');

        // Draft (pesan dulu, bayar nanti — per nomor meja)
        Route::post('/draft',                  [TransactionController::class, 'draftStore'])->name('draft.store');
        Route::get('/draft/{id}',              [TransactionController::class, 'draftShow'])->name('draft.show');
        Route::post('/draft/{id}/checkout',    [TransactionController::class, 'draftCheckout'])->name('draft.checkout');
        Route::delete('/draft/{id}',           [TransactionController::class, 'draftDestroy'])->name('draft.destroy');

        Route::get('/{id}/struk',     [TransactionController::class, 'struk'])->name('struk');
        Route::get('/{id}/struk-pdf', [TransactionController::class, 'strukPdf'])->name('struk.pdf');
        Route::get('/{id}/escpos',    [TransactionController::class, 'escpos'])->name('escpos');
        Route::get('/{id}/whatsapp',  [TransactionController::class, 'whatsapp'])->name('whatsapp');
    });

    // Laporan
    Route::prefix('laporan')->name('tenant.laporan.')->middleware('permission:laporan')->group(function () {
        Route::get('/',       [TransactionController::class, 'laporan'])->name('index');
        Route::get('/export', [TransactionController::class, 'export'])->name('export');
        Route::post('/{id}/cancel', [TransactionController::class, 'cancel'])->name('cancel');
    });

    // Stok
    Route::prefix('stok')->name('tenant.stok.')->middleware('permission:stok')->group(function () {
        Route::get('/',                  [StockController::class, 'index'])->name('index');
        Route::get('/riwayat',           [StockController::class, 'allHistory'])->name('history.all');
        Route::get('/{product}',         [StockController::class, 'show'])->name('show');
        Route::put('/{product}',         [StockController::class, 'update'])->name('update');
        Route::get('/{product}/riwayat', [StockController::class, 'history'])->name('history');
    });

    // Pelanggan
    Route::prefix('pelanggan')->name('tenant.pelanggan.')->middleware('permission:pelanggan')->group(function () {
        Route::get('/',            [CustomerController::class, 'index'])->name('index');
        Route::post('/',           [CustomerController::class, 'store'])->name('store');
        Route::get('/search',      [CustomerController::class, 'search'])->name('search');
        Route::get('/{customer}',  [CustomerController::class, 'show'])->name('show');
        Route::put('/{customer}',  [CustomerController::class, 'update'])->name('update');
    });

    // Promo
    Route::prefix('promo')->name('tenant.promo.')->middleware('permission:promo')->group(function () {
        Route::get('/',              [PromoController::class, 'index'])->name('index');
        Route::post('/',             [PromoController::class, 'store'])->name('store');
        Route::put('/{promo}/toggle',[PromoController::class, 'toggle'])->name('toggle');
        Route::delete('/{promo}',    [PromoController::class, 'destroy'])->name('destroy');
        Route::post('/calculate',    [PromoController::class, 'calculate'])->name('calculate');
    });



    // Pengeluaran
    Route::prefix('expenses')->name('tenant.expenses.')->middleware('permission:pengeluaran')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
    });

    // Hutang Piutang
    Route::prefix('hutang')->name('tenant.hutang.')->middleware('permission:hutang')->group(function () {
        Route::get('/',          [DebtController::class, 'index'])->name('index');
        Route::get('/riwayat',   [DebtController::class, 'history'])->name('history');
        Route::post('/',         [DebtController::class, 'store'])->name('store');
        Route::post('/{debt}/bayar', [DebtController::class, 'pay'])->name('pay');
    });

    // Shift
    Route::prefix('shift')->name('tenant.shift.')->middleware('permission:shift')->group(function () {
        Route::get('/',           [ShiftController::class, 'index'])->name('index');
        Route::post('/buka',      [ShiftController::class, 'open'])->name('open');
        Route::post('/{shift}/tutup', [ShiftController::class, 'close'])->name('close');
        Route::get('/{shift}',    [ShiftController::class, 'show'])->name('show');
    });

    // Gudang Bahan Baku — pembukuan stok bahan (akses via permission)
    Route::middleware('permission:bahan')->prefix('bahan')->name('tenant.bahan.')->group(function () {
        Route::get('/',                  [RawMaterialController::class, 'index'])->name('index');
        Route::post('/',                 [RawMaterialController::class, 'store'])->name('store');
        Route::put('/{bahan}',           [RawMaterialController::class, 'update'])->name('update');
        Route::post('/{bahan}/adjust',   [RawMaterialController::class, 'adjust'])->name('adjust');
        Route::get('/{bahan}/riwayat',   [RawMaterialController::class, 'history'])->name('history');
        Route::delete('/{bahan}',        [RawMaterialController::class, 'destroy'])->name('destroy');
    });

    // Owner only — kelola tim, langganan & pengaturan toko
    Route::middleware('role:owner')->prefix('users')->name('tenant.users.')->group(function () {
        Route::get('/',                      [UserController::class, 'index'])->name('index');
        Route::post('/',                     [UserController::class, 'store'])->name('store');
        Route::put('/{user}/toggle',         [UserController::class, 'toggleActive'])->name('toggle');
        Route::put('/{user}/permissions',    [UserController::class, 'updatePermissions'])->name('permissions');
        Route::put('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}',             [UserController::class, 'destroy'])->name('destroy');
    });
    Route::middleware('role:owner')->group(function () {
        Route::get('/profil', [ProfileController::class, 'index'])->name('tenant.profil');
        Route::put('/profil', [ProfileController::class, 'update'])->name('tenant.profil.update');
    });

    // Ganti password sendiri — boleh diakses semua role yang login (owner/admin/kasir)
    Route::get('/ganti-password',  [ProfileController::class, 'editPassword'])->name('tenant.password.edit');
    Route::put('/ganti-password',  [ProfileController::class, 'updatePassword'])->name('tenant.password.update');

    Route::get('/subscription/expired', fn() => view('tenant.subscription.expired'))->name('tenant.subscription.expired');
});

// BILLING — sengaja di luar middleware 'subscription' agar tetap bisa diakses
// ketika trial/langganan sudah habis (justru saat itulah user perlu membayar).
Route::middleware(['auth', 'tenant', 'role:owner'])->prefix('billing')->name('tenant.billing.')->group(function () {
    Route::get('/',                         [BillingController::class, 'index'])->name('index');
    Route::post('/invoice',                 [BillingController::class, 'createInvoice'])->name('invoice');
    Route::post('/{invoice}/bukti',         [BillingController::class, 'uploadProof'])->name('proof');
});

// SUPER ADMIN
Route::middleware(['auth','role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'index'])->name('dashboard');
    Route::get('/tenants',                   [SuperAdminController::class, 'tenants'])->name('tenants');
    Route::post('/tenants',                  [SuperAdminController::class, 'storeTenant'])->name('tenants.store');
    Route::get('/tenants/{tenant}',          [SuperAdminController::class, 'tenantDetail'])->name('tenants.detail');
    Route::put('/tenants/{tenant}/suspend',  [SuperAdminController::class, 'suspend'])->name('tenants.suspend');
    Route::put('/tenants/{tenant}/approve',  [SuperAdminController::class, 'approve'])->name('tenants.approve');
    Route::put('/tenants/{tenant}/reject',   [SuperAdminController::class, 'reject'])->name('tenants.reject');
    Route::put('/tenants/{tenant}/status',   [SuperAdminController::class, 'updateStatus'])->name('tenants.status');
    Route::put('/tenants/{tenant}/edit',      [SuperAdminController::class, 'updateTenant'])->name('tenants.update');
    Route::put('/tenants/{tenant}/extend',    [SuperAdminController::class, 'extendTrial'])->name('tenants.extend');
    Route::put('/tenants/{tenant}/stop-trial',[SuperAdminController::class, 'stopTrial'])->name('tenants.stop-trial');

    // Verifikasi pembayaran
    Route::get('/pembayaran',                  [SuperAdminController::class, 'payments'])->name('payments');
    Route::put('/pembayaran/{invoice}/konfirmasi', [SuperAdminController::class, 'confirmPayment'])->name('payments.confirm');
    Route::get('/laporan', [SuperAdminController::class, 'laporan'])->name('laporan');

    // Master Harga
    Route::get('/harga',                 [PricingController::class, 'index'])->name('pricing.index');
    Route::post('/harga',                [PricingController::class, 'store'])->name('pricing.store');
    Route::put('/harga/{plan}',          [PricingController::class, 'update'])->name('pricing.update');
    Route::put('/harga/{plan}/toggle',   [PricingController::class, 'toggle'])->name('pricing.toggle');
    Route::delete('/harga/{plan}',       [PricingController::class, 'destroy'])->name('pricing.destroy');
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
    Route::put('/settings', [SuperAdminController::class, 'updateSettings'])->name('settings.update');

    // Ganti password akun super admin
    Route::get('/ganti-password', [SuperAdminController::class, 'editPassword'])->name('password.edit');
    Route::put('/ganti-password', [SuperAdminController::class, 'updatePassword'])->name('password.update');
    Route::get('/maintenance', [SuperAdminController::class, 'maintenance'])->name('maintenance');
    Route::post('/maintenance/migrate', [SuperAdminController::class, 'runMigrate'])->name('maintenance.migrate');
    Route::post('/maintenance/storage-link', [SuperAdminController::class, 'runStorageLink'])->name('maintenance.storage-link');
    Route::post('/maintenance/optimize-clear', [SuperAdminController::class, 'runOptimizeClear'])->name('maintenance.optimize-clear');
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
});
