<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments');
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->decimal('allocated_amount', 15, 2);

            $table->unique(['payment_id', 'invoice_id'], 'uq_payment_invoice');
        });

        DB::statement('ALTER TABLE payment_allocations ADD CONSTRAINT chk_allocated_amount_positive CHECK (allocated_amount > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
