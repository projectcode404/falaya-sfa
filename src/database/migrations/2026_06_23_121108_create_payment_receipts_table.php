<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 30)->unique();
            $table->foreignId('payment_id')->constrained('payments');
            $table->foreignId('customer_id')->constrained('customers');

            $table->string('customer_name_snapshot', 100);
            $table->string('collector_name_snapshot', 100);
            $table->decimal('total_paid', 15, 2);
            $table->decimal('remaining_after', 15, 2);

            $table->date('receipt_date');
            $table->string('qr_payload', 500);
            $table->unsignedBigInteger('pdf_media_id')->nullable();
            $table->timestamp('downloaded_at')->nullable();

            $table->string('status', 20)->default('DRAFT');
            // DRAFT, POSTED, VOID

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
