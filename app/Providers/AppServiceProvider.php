<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AppSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

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
        View::composer('*', function ($view) {
            $appSettings = [
                'app_name' => 'Tokaku',
                'app_logo_path' => null,
            ];

            try {
                if (Schema::hasTable('app_settings')) {
                    $appSettings['app_name'] = AppSetting::getValue('app_name', 'Tokaku');
                    $appSettings['app_logo_path'] = AppSetting::getValue('app_logo_path');
                }
            } catch (\Throwable $e) {
                // Abaikan saat proses migrate/install awal.
            }

            $view->with('appSettings', $appSettings);
        });
    }
}
