<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_cash_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salesman_id')->constrained('users');
            $table->date('operational_date');

            $table->decimal('cash_sales_total', 15, 2)->default(0);
            $table->decimal('collection_cash_total', 15, 2)->default(0);
            $table->decimal('system_total', 15, 2);

            $table->decimal('actual_received', 15, 2)->nullable();
            $table->decimal('difference', 15, 2)->nullable();

            $table->string('status', 20)->default('PENDING');
            // PENDING, RECONCILED, DISCREPANCY

            $table->text('discrepancy_notes')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users');
            $table->timestamp('reconciled_at')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['salesman_id', 'operational_date'], 'uq_daily_recon');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_cash_reconciliations');
    }
};
