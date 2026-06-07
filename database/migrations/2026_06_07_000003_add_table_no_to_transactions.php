<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Nomor meja — opsional (mis. untuk resto/cafe). Null = tidak dipakai.
            $table->string('table_no', 20)->nullable()->after('invoice_no');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('table_no');
        });
    }
};
