<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Master bahan baku (gudang). Terpisah dari produk jual; murni pembukuan.
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('unit', 30)->default('pcs'); // satuan: pcs, kg, gram, liter, dll
            $table->integer('stock')->default(0);        // stok saat ini
            $table->integer('low_stock_alert')->default(0);
            $table->string('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['tenant_id', 'is_active']);
        });

        // Riwayat keluar/masuk bahan baku.
        Schema::create('raw_material_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('qty_before');
            $table->integer('qty_change');               // + masuk, - keluar
            $table->integer('qty_after');
            $table->enum('type', ['in', 'out', 'adjustment'])->default('out');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'raw_material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_material_logs');
        Schema::dropIfExists('raw_materials');
    }
};
