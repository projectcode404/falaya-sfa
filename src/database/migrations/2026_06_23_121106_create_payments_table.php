<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 30)->unique();

            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('collected_by')->constrained('users');
            // salesman (CASH) atau admin (TRANSFER)

            $table->foreignId('visit_plan_id')->nullable()->constrained('visit_plans');
            // NULL jika diinput Admin dari web (transfer)

            $table->date('operational_date');
            $table->string('payment_method', 10); // CASH, TRANSFER
            $table->decimal('total_amount', 15, 2);
            $table->text('notes')->nullable();

            $table->string('status', 20)->default('DRAFT');
            // DRAFT, POSTED, VOID

            $table->uuid('idempotency_key')->nullable()->unique();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('void_by')->nullable()->constrained('users');
            $table->timestamp('void_at')->nullable();
            $table->text('void_reason')->nullable();
        });

        DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payment_amount_positive CHECK (total_amount > 0)');
        DB::statement('CREATE INDEX idx_payments_customer ON payments(customer_id)');
        DB::statement('CREATE INDEX idx_payments_date ON payments(operational_date, payment_method)');
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
