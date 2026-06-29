<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'pinned_targets')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->json('pinned_targets')->nullable()->after('is_pinned');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'pinned_targets')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('pinned_targets');
            });
        }
    }
};
