<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->bigInteger('opening_cash')->default(0);    // kas awal
            $table->bigInteger('closing_cash')->nullable();    // kas akhir (diisi saat tutup)
            $table->bigInteger('expected_cash')->nullable();   // seharusnya (otomatis)
            $table->bigInteger('cash_difference')->nullable(); // selisih
            $table->integer('total_transactions')->default(0);
            $table->bigInteger('total_revenue')->default(0);
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'closed_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('shifts'); }
};
