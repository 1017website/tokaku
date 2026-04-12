<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'name'          => 'Warung Budi',
                'subdomain'     => 'warungbudi',
                'phone'         => '081234567890',
                'address'       => 'Jl. Mawar No. 10, Surabaya',
                'status'        => 'active',
                'trial_ends_at' => null,
            ],
            [
                'name'          => 'Toko Ani',
                'subdomain'     => 'tokoani',
                'phone'         => '082233445566',
                'address'       => 'Jl. Melati No. 5, Jakarta',
                'status'        => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::create($tenantData);

            // Buat user owner untuk tiap tenant
            User::create([
                'tenant_id' => $tenant->id,
                'name'      => 'Owner ' . $tenant->name,
                'email'     => 'owner@' . $tenant->subdomain . '.com',
                'password'  => Hash::make('password'),
                'role'      => 'owner',
                'is_active' => true,
            ]);

            // Buat user kasir untuk tiap tenant
            User::create([
                'tenant_id' => $tenant->id,
                'name'      => 'Kasir ' . $tenant->name,
                'email'     => 'kasir@' . $tenant->subdomain . '.com',
                'password'  => Hash::make('password'),
                'role'      => 'cashier',
                'is_active' => true,
            ]);
        }
    }
}
