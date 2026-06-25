<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_returns', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 30)->unique();

            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->constrained('users');

            $table->string('reason', 20); // RUSAK, EXPIRED
            $table->unsignedBigInteger('photo_media_id')->nullable();

            $table->decimal('total_amount', 15, 2);
            $table->string('refund_type', 20); // CREDIT_NOTE, REFUND -- fix: 20 bukan 10

            $table->string('status', 20)->default('PENDING_APPROVAL');

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('approval_notes')->nullable();

            $table->foreignId('refund_processed_by')->nullable()->constrained('users');
            $table->timestamp('refund_processed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_returns');
    }
};
