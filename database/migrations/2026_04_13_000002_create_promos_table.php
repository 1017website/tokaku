<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['percent', 'fixed', 'buyxgety']); // % diskon, nominal, beli X gratis Y
            $table->decimal('value', 12, 2)->default(0);      // nilai diskon
            $table->integer('min_qty')->default(1);            // untuk buyxgety: beli X
            $table->integer('free_qty')->default(0);           // untuk buyxgety: gratis Y
            $table->bigInteger('min_transaction')->default(0); // minimal total transaksi
            $table->bigInteger('max_discount')->default(0);    // maks diskon (0 = unlimited)
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); // promo khusus produk
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('promos'); }
};
