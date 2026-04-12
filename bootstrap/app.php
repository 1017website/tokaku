<?php

use App\Http\Middleware\TenantMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SubscriptionMiddleware;
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
        $middleware->web(append: [
            TenantMiddleware::class,
        ]);
        $middleware->alias([
            'tenant'       => TenantMiddleware::class,
            'role'         => RoleMiddleware::class,
            'subscription' => SubscriptionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
