<?php

use App\Http\Middleware\TenantMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SubscriptionMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant'       => TenantMiddleware::class,
            'role'         => RoleMiddleware::class,
            'subscription' => SubscriptionMiddleware::class,
            'permission'   => PermissionMiddleware::class,
        ]);

        // Jalankan TenantMiddleware SEBELUM SubstituteBindings agar global scope
        // BelongsToTenant sudah aktif ketika route-model-binding ({shift}, {product},
        // {customer}, dst.) me-resolve model. Tanpa ini, binding mengabaikan filter
        // tenant dan bisa memunculkan 403/data milik tenant lain.
        $middleware->priority([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
            TenantMiddleware::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            SubscriptionMiddleware::class,
            RoleMiddleware::class,
            PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
