<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('role');
        });

        // Isi default permission untuk user yang sudah ada (selain owner/superadmin)
        // agar tidak terkunci dari semua menu setelah update.
        $defaults = config('permissions.defaults', []);

        foreach (['admin', 'cashier'] as $role) {
            if (!empty($defaults[$role])) {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('role', $role)
                    ->whereNull('permissions')
                    ->update(['permissions' => json_encode($defaults[$role])]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
