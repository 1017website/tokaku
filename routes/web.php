<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\TransactionController;
use App\Http\Controllers\Tenant\ProfileController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use Illuminate\Support\Facades\Route;

// Root
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'superadmin'
            ? redirect()->route('superadmin.dashboard')
            : redirect()->route('tenant.dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ============================================================
// TENANT ROUTES
// ============================================================
Route::middleware(['auth', 'tenant', 'subscription'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

    // Produk + detail/riwayat
    Route::resource('products', ProductController::class)->names('tenant.products');

    // Kategori
    Route::resource('categories', CategoryController::class)->names('tenant.categories');

    // Kasir
    Route::prefix('kasir')->name('tenant.kasir.')->group(function () {
        Route::get('/',                [TransactionController::class, 'index'])->name('index');
        Route::post('/proses',         [TransactionController::class, 'proses'])->name('proses');
        Route::get('/{id}/struk',      [TransactionController::class, 'struk'])->name('struk');
        Route::get('/{id}/struk-pdf',  [TransactionController::class, 'strukPdf'])->name('struk.pdf');
    });

    // Laporan
    Route::prefix('laporan')->name('tenant.laporan.')->group(function () {
        Route::get('/',       [TransactionController::class, 'laporan'])->name('index');
        Route::get('/export', [TransactionController::class, 'export'])->name('export');
    });

    // Manajemen User — hanya owner & admin
    Route::middleware('role:owner,admin')->prefix('users')->name('tenant.users.')->group(function () {
        Route::get('/',                     [UserController::class, 'index'])->name('index');
        Route::post('/',                    [UserController::class, 'store'])->name('store');
        Route::put('/{user}/toggle',        [UserController::class, 'toggleActive'])->name('toggle');
        Route::put('/{user}/reset-password',[UserController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}',            [UserController::class, 'destroy'])->name('destroy');
    });

    // Profil toko — hanya owner & admin
    Route::middleware('role:owner,admin')->group(function () {
        Route::get('/profil', [ProfileController::class, 'index'])->name('tenant.profil');
        Route::put('/profil', [ProfileController::class, 'update'])->name('tenant.profil.update');
    });

    Route::get('/subscription/expired', fn() => view('tenant.subscription.expired'))
        ->name('tenant.subscription.expired');
});

// ============================================================
// SUPER ADMIN ROUTES
// ============================================================
Route::middleware(['auth', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/',         [SuperAdminController::class, 'index'])->name('dashboard');
        Route::get('/tenants',  [SuperAdminController::class, 'tenants'])->name('tenants');
        Route::post('/tenants', [SuperAdminController::class, 'storeTenant'])->name('tenants.store');
        Route::put('/tenants/{tenant}/suspend', [SuperAdminController::class, 'suspend'])->name('tenants.suspend');
    });
