<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $host      = $request->getHost();
        $baseDomain = config('app.base_domain'); // tokaku.1017studios.id

        // Ambil subdomain terdepan
        // warungbudi.tokaku.1017studios.id → warungbudi
        $subdomain = str_replace('.' . $baseDomain, '', $host);

        if ($subdomain === $host || $subdomain === '') {
            // Bukan subdomain klien — lanjut tanpa tenant (landing page)
            return $next($request);
        }

        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', '!=', 'suspended')
            ->firstOrFail();

        // Bind tenant ke service container — dipakai BelongsToTenant trait
        app()->instance('currentTenant', $tenant);

        // Share ke semua view Blade
        view()->share('currentTenant', $tenant);

        return $next($request);
    }
}
