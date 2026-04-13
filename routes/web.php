<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\TransactionController;
use App\Http\Controllers\Tenant\ProfileController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\StockController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'superadmin'
            ? redirect()->route('superadmin.dashboard')
            : redirect()->route('tenant.dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Tenant Routes
Route::middleware(['auth', 'tenant', 'subscription'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
    Route::resource('products', ProductController::class)->names('tenant.products');
    Route::resource('categories', CategoryController::class)->names('tenant.categories');

    // Manajemen Stok
    Route::prefix('stok')->name('tenant.stok.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::put('/{product}', [StockController::class, 'update'])->name('update');
        Route::get('/riwayat', [StockController::class, 'allHistory'])->name('history.all');
        Route::get('/{product}/riwayat', [StockController::class, 'history'])->name('history');
    });

    Route::prefix('kasir')->name('tenant.kasir.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::post('/proses', [TransactionController::class, 'proses'])->name('proses');
        Route::get('/{id}/struk', [TransactionController::class, 'struk'])->name('struk');
        Route::get('/{id}/struk-pdf', [TransactionController::class, 'strukPdf'])->name('struk.pdf');
    });

    Route::prefix('laporan')->name('tenant.laporan.')->group(function () {
        Route::get('/', [TransactionController::class, 'laporan'])->name('index');
        Route::get('/export', [TransactionController::class, 'export'])->name('export');
    });

    Route::middleware('role:owner,admin')->prefix('users')->name('tenant.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}/toggle', [UserController::class, 'toggleActive'])->name('toggle');
        Route::put('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('role:owner,admin')->group(function () {
        Route::get('/profil', [ProfileController::class, 'index'])->name('tenant.profil');
        Route::put('/profil', [ProfileController::class, 'update'])->name('tenant.profil.update');
    });

    Route::get('/subscription/expired', fn() => view('tenant.subscription.expired'))
        ->name('tenant.subscription.expired');
});

// Super Admin Routes
Route::middleware(['auth', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/', [SuperAdminController::class, 'index'])->name('dashboard');

        Route::get('/tenants', [SuperAdminController::class, 'tenants'])->name('tenants');
        Route::post('/tenants', [SuperAdminController::class, 'storeTenant'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [SuperAdminController::class, 'tenantDetail'])->name('tenants.detail');
        Route::put('/tenants/{tenant}/suspend', [SuperAdminController::class, 'suspend'])->name('tenants.suspend');
        Route::put('/tenants/{tenant}/status', [SuperAdminController::class, 'updateStatus'])->name('tenants.status');

        Route::get('/laporan', [SuperAdminController::class, 'laporan'])->name('laporan');

        Route::get('/users', function () {
            $users = \App\Models\User::with('tenant')
                ->when(request('search'), fn($q) => $q->where('name', 'like', '%' . request('search') . '%')
                    ->orWhere('email', 'like', '%' . request('search') . '%'))
                ->orderByDesc('created_at')
                ->paginate(20);
            return view('superadmin.users', compact('users'));
        })->name('users');
    });
