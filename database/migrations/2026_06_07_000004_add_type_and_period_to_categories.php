<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Tipe kategori: regular (tanpa masa berlaku), promo, bundling.
            // Data lama otomatis 'regular' karena default.
            $table->enum('type', ['regular', 'promo', 'bundling'])->default('regular')->after('name');

            // Masa berlaku — hanya relevan untuk promo/bundling, boleh null (tanpa batas).
            $table->dateTime('starts_at')->nullable()->after('type');
            $table->dateTime('ends_at')->nullable()->after('starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['type', 'starts_at', 'ends_at']);
        });
    }
};
