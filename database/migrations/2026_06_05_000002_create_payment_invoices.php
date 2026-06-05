<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_plan_id')->nullable()->constrained('pricing_plans')->nullOnDelete();
            $table->string('invoice_no')->unique();          // INV-PAY-YYYYMMDD-0001
            $table->unsignedBigInteger('base_amount');        // harga paket
            $table->unsignedSmallInteger('unique_code');      // 3 digit unik (1-999)
            $table->unsignedBigInteger('total_amount');       // base + unique_code (yang ditransfer)
            $table->unsignedInteger('duration_months');       // durasi paket
            $table->enum('status', ['unpaid','waiting_confirmation','paid','rejected','expired'])->default('unpaid');
            $table->string('proof_path')->nullable();         // bukti transfer (upload user)
            $table->timestamp('paid_at')->nullable();         // saat user klaim bayar
            $table->timestamp('confirmed_at')->nullable();    // saat superadmin verifikasi
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_invoices');
    }
};
