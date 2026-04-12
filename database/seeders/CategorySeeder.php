<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Makanan',
            'Minuman',
            'Snack',
            'Sembako',
            'Lainnya',
        ];

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            app()->instance('currentTenant', $tenant);

            foreach ($categories as $name) {
                Category::create([
                    'tenant_id' => $tenant->id,
                    'name'      => $name,
                ]);
            }
        }

        // Hapus binding setelah selesai
        app()->forgetInstance('currentTenant');
    }
}
