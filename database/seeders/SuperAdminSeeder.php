<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'tenant_id' => null,
            'name'      => 'Super Admin',
            'email'     => 'admin@1017studios.id',
            'password'  => Hash::make('password'),
            'role'      => 'superadmin',
            'is_active' => true,
        ]);
    }
}
