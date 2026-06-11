<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('status', ['completed', 'cancelled'])->default('completed')->after('payment_status');
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->string('cancel_reason')->nullable()->after('cancelled_by');
        });

        // Tambah opsi 'cancel' pada enum type stock_logs (untuk jejak pembatalan).
        DB::statement("ALTER TABLE stock_logs MODIFY COLUMN type ENUM('restock','adjustment','sale','correction','cancel') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['status', 'cancelled_at', 'cancelled_by', 'cancel_reason']);
        });

        DB::statement("ALTER TABLE stock_logs MODIFY COLUMN type ENUM('restock','adjustment','sale','correction') NOT NULL");
    }
};
