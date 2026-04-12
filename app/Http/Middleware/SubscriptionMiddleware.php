<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!app()->has('currentTenant')) {
            return $next($request);
        }

        if (!app('currentTenant')->isActive()) {
            return redirect()->route('tenant.subscription.expired');
        }

        return $next($request);
    }
}
