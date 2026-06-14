<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Harga per satuan bahan baku (harga acuan / harga beli terakhir).
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->default(0)->after('unit');
        });

        // Harga per satuan SAAT pergerakan dicatat — dibekukan agar nilai
        // riwayat & summary harian tidak ikut berubah ketika harga master diubah.
        Schema::table('raw_material_logs', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->default(0)->after('qty_after');
        });
    }

    public function down(): void
    {
        Schema::table('raw_material_logs', function (Blueprint $table) {
            $table->dropColumn('price');
        });
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
