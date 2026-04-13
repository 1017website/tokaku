<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->foreignId('promo_id')->nullable()->after('shift_id')->constrained()->nullOnDelete();
            $table->decimal('tax_rate', 5, 2)->default(0)->after('tax'); // % PPN
            $table->enum('payment_status', ['paid', 'debt'])->default('paid')->after('payment_method');
        });
    }
    public function down(): void {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['shift_id']);
            $table->dropForeign(['promo_id']);
            $table->dropColumn(['customer_id','shift_id','promo_id','tax_rate','payment_status']);
        });
    }
};
