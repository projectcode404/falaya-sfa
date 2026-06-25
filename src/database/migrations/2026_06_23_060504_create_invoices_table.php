<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->foreignId('sales_order_id')->constrained('sales_orders');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->constrained('users');

            // Snapshot
            $table->string('customer_name_snapshot', 100);
            $table->text('customer_address_snapshot');
            $table->string('receiver_name', 100);

            $table->date('invoice_date');
            $table->date('due_date');
            $table->smallInteger('credit_term_days_snapshot');

            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2);

            $table->string('status', 20)->default('UNPAID');
            // UNPAID, PARTIAL, PAID, OVERDUE

            $table->unsignedBigInteger('pdf_media_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement('ALTER TABLE invoices ADD CONSTRAINT chk_invoice_remaining CHECK (remaining_amount >= 0)');
        DB::statement('ALTER TABLE invoices ADD CONSTRAINT chk_invoice_paid CHECK (paid_amount >= 0)');
        DB::statement('CREATE INDEX idx_invoices_customer_status ON invoices(customer_id, status)');
        DB::statement('CREATE INDEX idx_invoices_due_date ON invoices(due_date) WHERE status != \'PAID\'');
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
