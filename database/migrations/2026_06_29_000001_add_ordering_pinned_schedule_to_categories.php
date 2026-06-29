<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Cek per-kolom agar aman dijalankan ulang (idempotent).
            if (!Schema::hasColumn('categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('categories', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('sort_order');
            }
            if (!Schema::hasColumn('categories', 'schedule_days')) {
                $table->json('schedule_days')->nullable()->after('is_pinned');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            foreach (['sort_order', 'is_pinned', 'schedule_days'] as $col) {
                if (Schema::hasColumn('categories', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
