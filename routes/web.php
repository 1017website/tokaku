<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\TransactionController;
use App\Http\Controllers\Tenant\ProfileController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing Page — tokaku.1017studios.id
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (app()->has('currentTenant')) {
        return redirect()->route('tenant.dashboard');
    }
    return redirect()->route('login'); // ← ganti ini
})->name('home');

/*
|--------------------------------------------------------------------------
| Auth Routes — berlaku untuk tenant dan superadmin
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Tenant Routes — hanya bisa diakses dari subdomain klien
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'subscription'])->prefix('')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('tenant.dashboard');

    // Produk
    Route::resource('products', ProductController::class)
        ->names('tenant.products');

    // Kategori
    Route::resource('categories', CategoryController::class)
        ->names('tenant.categories');

    // Transaksi / Kasir
    Route::prefix('kasir')->name('tenant.kasir.')->group(function () {
        Route::get('/',         [TransactionController::class, 'index'])->name('index');
        Route::post('/proses',  [TransactionController::class, 'proses'])->name('proses');
        Route::get('/{id}/struk', [TransactionController::class, 'struk'])->name('struk');
    });

    // Laporan
    Route::prefix('laporan')->name('tenant.laporan.')->group(function () {
        Route::get('/',          [TransactionController::class, 'laporan'])->name('index');
        Route::get('/export',    [TransactionController::class, 'export'])->name('export');
    });

    // Profil & pengaturan toko (hanya owner)
    Route::middleware('role:owner,admin')->group(function () {
        Route::get('/profil',    [ProfileController::class, 'index'])->name('tenant.profil');
        Route::put('/profil',    [ProfileController::class, 'update'])->name('tenant.profil.update');
    });

    Route::get('/subscription/expired', function () {
        return view('tenant.subscription.expired');
    })->name('tenant.subscription.expired');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes — hanya bisa diakses dari domain utama
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/',         [SuperAdminController::class, 'index'])->name('dashboard');
        Route::get('/tenants',  [SuperAdminController::class, 'tenants'])->name('tenants');
        Route::post('/tenants', [SuperAdminController::class, 'storeTenant'])->name('tenants.store');
        Route::put('/tenants/{tenant}/suspend', [SuperAdminController::class, 'suspend'])->name('tenants.suspend');
    });
