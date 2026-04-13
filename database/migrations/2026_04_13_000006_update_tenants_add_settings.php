<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('tax_enabled')->default(false)->after('address');
            $table->decimal('tax_rate', 5, 2)->default(11)->after('tax_enabled'); // PPN 11%
            $table->string('tax_name')->default('PPN')->after('tax_rate');
        });
    }
    public function down(): void {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['tax_enabled','tax_rate','tax_name']);
        });
    }
};
