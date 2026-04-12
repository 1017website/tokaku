<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $productTemplates = [
            ['name' => 'Nasi Goreng',       'sku' => 'MKN-001', 'price' => 15000,  'cost' => 8000,  'stock' => 100, 'cat' => 'Makanan'],
            ['name' => 'Mie Goreng',         'sku' => 'MKN-002', 'price' => 13000,  'cost' => 7000,  'stock' => 100, 'cat' => 'Makanan'],
            ['name' => 'Ayam Goreng',        'sku' => 'MKN-003', 'price' => 18000,  'cost' => 10000, 'stock' => 50,  'cat' => 'Makanan'],
            ['name' => 'Es Teh Manis',       'sku' => 'MNM-001', 'price' => 5000,   'cost' => 2000,  'stock' => 200, 'cat' => 'Minuman'],
            ['name' => 'Es Jeruk',           'sku' => 'MNM-002', 'price' => 7000,   'cost' => 3000,  'stock' => 150, 'cat' => 'Minuman'],
            ['name' => 'Aqua 600ml',         'sku' => 'MNM-003', 'price' => 4000,   'cost' => 2500,  'stock' => 48,  'cat' => 'Minuman'],
            ['name' => 'Chitato',            'sku' => 'SNK-001', 'price' => 10000,  'cost' => 7000,  'stock' => 30,  'cat' => 'Snack'],
            ['name' => 'Taro',               'sku' => 'SNK-002', 'price' => 8000,   'cost' => 5500,  'stock' => 30,  'cat' => 'Snack'],
            ['name' => 'Beras 5kg',          'sku' => 'SMB-001', 'price' => 75000,  'cost' => 65000, 'stock' => 20,  'cat' => 'Sembako'],
            ['name' => 'Minyak Goreng 1L',   'sku' => 'SMB-002', 'price' => 18000,  'cost' => 15000, 'stock' => 3,   'cat' => 'Sembako'],
        ];

        foreach ($tenants as $tenant) {
            app()->instance('currentTenant', $tenant);

            foreach ($productTemplates as $p) {
                $category = Category::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->where('name', $p['cat'])
                    ->first();

                Product::create([
                    'tenant_id'       => $tenant->id,
                    'category_id'     => $category?->id,
                    'name'            => $p['name'],
                    'sku'             => $p['sku'],
                    'price'           => $p['price'],
                    'cost_price'      => $p['cost'],
                    'stock'           => $p['stock'],
                    'low_stock_alert' => 5,
                    'is_active'       => true,
                ]);
            }
        }

        app()->forgetInstance('currentTenant');
    }
}
