<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'print_mode')) {
                // auto = deteksi otomatis, rawbt = Android/Tablet, qz = QZ Tray (PC)
                $table->string('print_mode', 10)->default('auto')->after('tax_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'print_mode')) {
                $table->dropColumn('print_mode');
            }
        });
    }
};
