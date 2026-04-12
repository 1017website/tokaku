<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kalau tidak ada tenant (akses dari domain utama), skip
        if (!app()->has('currentTenant')) {
            return $next($request);
        }

        $tenant = app('currentTenant');

        if (!$tenant->isActive()) {
            return redirect()->route('tenant.subscription.expired');
        }

        return $next($request);
    }
}
