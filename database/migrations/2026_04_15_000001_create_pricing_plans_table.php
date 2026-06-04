<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pricing_plans')) {
            Schema::create('pricing_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');                       // mis. "1 Bulan"
                $table->string('tagline')->nullable();        // mis. "Untuk coba-coba dulu"
                $table->unsignedInteger('duration_months');   // 1, 6, 12
                $table->decimal('original_price', 12, 2)->default(0); // harga asli (coret)
                $table->decimal('price', 12, 2)->default(0);          // harga setelah diskon
                $table->boolean('is_popular')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('cta_label')->default('Mulai Sekarang');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
