<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host       = $request->getHost();
        $baseDomain = config('tokaku.base_domain');

        // Cek apakah request dari subdomain klien
        // warungbudi.tokaku.1017studios.id → warungbudi
        if (!str_ends_with($host, '.' . $baseDomain)) {
            return $next($request);
        }

        $subdomain = str_replace('.' . $baseDomain, '', $host);

        if (empty($subdomain)) {
            return $next($request);
        }

        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            abort(404, 'Toko tidak ditemukan.');
        }

        if ($tenant->status === 'suspended') {
            abort(403, 'Akun toko Anda telah ditangguhkan. Hubungi admin.');
        }

        if (!$tenant->isActive()) {
            abort(403, 'Masa trial Anda telah berakhir. Silakan upgrade paket.');
        }

        app()->instance('currentTenant', $tenant);
        view()->share('currentTenant', $tenant);

        return $next($request);
    }
}
