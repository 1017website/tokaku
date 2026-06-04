<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'            => '1 Bulan',
                'tagline'         => 'Cocok untuk mulai mencoba',
                'duration_months' => 1,
                'original_price'  => 300000,
                'price'           => 150000,
                'is_popular'      => false,
                'is_active'       => true,
                'sort_order'      => 1,
                'cta_label'       => 'Mulai Sekarang',
            ],
            [
                'name'            => '6 Bulan',
                'tagline'         => 'Paling populer & hemat',
                'duration_months' => 6,
                'original_price'  => 1800000,
                'price'           => 900000,
                'is_popular'      => true,
                'is_active'       => true,
                'sort_order'      => 2,
                'cta_label'       => 'Coba 14 Hari Gratis',
            ],
            [
                'name'            => '12 Bulan',
                'tagline'         => 'Termurah per bulan',
                'duration_months' => 12,
                'original_price'  => 3600000,
                'price'           => 1800000,
                'is_popular'      => false,
                'is_active'       => true,
                'sort_order'      => 3,
                'cta_label'       => 'Mulai Sekarang',
            ],
        ];

        foreach ($plans as $plan) {
            PricingPlan::updateOrCreate(
                ['duration_months' => $plan['duration_months']],
                $plan
            );
        }
    }
}
