<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AppSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pagination bertema putih-hijau (berlaku global di semua menu).
        Paginator::defaultView('vendor.pagination.tokaku');

        View::composer('*', function ($view) {
            $appSettings = [
                'app_name'         => 'Tokaku',
                'app_logo_path'    => null, // logo ikon (kotak)
                'app_logo_full'    => null, // logo full (dengan teks)
                'app_favicon'      => null,
                // SEO
                'seo_title'        => null,
                'seo_description'  => null,
                'seo_keywords'     => null,
                'seo_og_image'     => null,
                // Ads / Tracking
                'google_ads_id'        => null, // AW-XXXXXXXXX
                'google_analytics_id'  => null, // G-XXXXXXXXXX
                'meta_pixel_id'        => null, // 15-16 digit
                'gtm_id'               => null, // GTM-XXXXXXX
                // Floating WhatsApp (landing page)
                'whatsapp_float'       => null, // nomor format 62xxx
                'whatsapp_float_text'  => 'Halo, saya tertarik dengan Tokaku.',
            ];

            try {
                if (Schema::hasTable('app_settings')) {
                    foreach (array_keys($appSettings) as $key) {
                        $appSettings[$key] = AppSetting::getValue($key, $appSettings[$key]);
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan saat proses migrate/install awal.
            }

            $view->with('appSettings', $appSettings);
        });

        // Badge notifikasi sidebar super admin: jumlah tenant baru (pending)
        // dan invoice pembayaran yang menunggu konfirmasi.
        View::composer('superadmin.layout', function ($view) {
            $pendingTenants = 0;
            $waitingPayments = 0;
            try {
                if (Schema::hasTable('tenants')) {
                    $pendingTenants = \App\Models\Tenant::where('status', 'pending')->count();
                }
                if (Schema::hasTable('payment_invoices')) {
                    $waitingPayments = \App\Models\PaymentInvoice::where('status', 'waiting_confirmation')->count();
                }
            } catch (\Throwable $e) {
                // Abaikan saat migrate/install awal.
            }
            $view->with('saPendingTenants', $pendingTenants);
            $view->with('saWaitingPayments', $waitingPayments);
        });
    }
}
