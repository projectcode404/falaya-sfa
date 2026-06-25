<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_override_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders');
            $table->foreignId('customer_id')->constrained('customers');

            $table->decimal('outstanding_at_request', 15, 2);
            $table->decimal('order_amount', 15, 2);
            $table->decimal('credit_limit_at_request', 15, 2);

            $table->string('status', 20)->default('PENDING');
            // PENDING, APPROVED, REJECTED

            $table->foreignId('requested_by')->constrained('users');
            $table->timestamp('requested_at')->useCurrent();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('approval_notes')->nullable();
        });

        DB::statement('CREATE INDEX idx_credit_override_pending ON credit_override_requests(status) WHERE status = \'PENDING\'');
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_override_requests');
    }
};
