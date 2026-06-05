<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Perluas enum status: tambah 'pending' (menunggu approval) & 'rejected'.
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('pending','trial','active','suspended','rejected') NOT NULL DEFAULT 'pending'");

        Schema::table('tenants', function (Blueprint $table) {
            // Data dari form registrasi publik
            $table->string('business_type')->nullable()->after('address');   // jenis usaha
            $table->string('owner_name')->nullable()->after('business_type'); // nama pendaftar
            $table->string('owner_email')->nullable()->after('owner_name');
            $table->timestamp('approved_at')->nullable()->after('trial_ends_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->string('reject_reason')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['business_type','owner_name','owner_email','approved_at','rejected_at','reject_reason']);
        });
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('trial','active','suspended') NOT NULL DEFAULT 'trial'");
    }
};
