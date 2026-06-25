<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 30)->unique();

            $table->foreignId('visit_plan_id')->constrained('visit_plans');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->constrained('users');
            $table->date('operational_date');

            $table->string('payment_type', 10); // CASH, CREDIT
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total_amount', 15, 2);

            $table->string('status', 20)->default('DRAFT');
            // DRAFT, POSTED, CANCELLED, VOID

            $table->string('receiver_name', 100)->nullable();

            // Credit limit override
            $table->boolean('requires_override')->default(false);
            $table->string('override_status', 20)->nullable();
            // PENDING, APPROVED, REJECTED

            $table->uuid('idempotency_key')->nullable()->unique();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamp('updated_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->foreignId('void_by')->nullable()->constrained('users');
            $table->timestamp('void_at')->nullable();
            $table->text('void_reason')->nullable();
        });

        // Mencegah lebih dari satu SO aktif per Visit
        DB::statement('CREATE UNIQUE INDEX uq_one_active_so_per_visit ON sales_orders(visit_plan_id) WHERE status IN (\'DRAFT\', \'POSTED\')');
        DB::statement('CREATE INDEX idx_sales_orders_customer ON sales_orders(customer_id, status)');
        DB::statement('CREATE INDEX idx_sales_orders_salesman_date ON sales_orders(salesman_id, operational_date)');
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
