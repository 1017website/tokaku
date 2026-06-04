<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'initial_capital')) {
                $table->decimal('initial_capital', 15, 2)->default(0)->after('tax_name');
            }
        });
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('expense_date');
            $table->string('category');
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id','expense_date']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('expenses');
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'initial_capital')) $table->dropColumn('initial_capital');
        });
    }
};
