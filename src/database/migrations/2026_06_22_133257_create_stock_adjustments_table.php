<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 30)->unique();

            $table->foreignId('product_id')->constrained('products');
            $table->string('holder_type', 20);
            $table->unsignedBigInteger('holder_id')->nullable();

            $table->decimal('qty', 15, 3);
            $table->string('reason', 20);
            // RUSAK, EXPIRED, OPNAME_SELISIH
            $table->text('notes')->nullable();

            $table->string('source_context', 30);
            // WAREHOUSE_OPNAME, UNLOADING_INSPECTION

            $table->string('status', 20)->default('PENDING_APPROVAL');
            // PENDING_APPROVAL, APPROVED, REJECTED

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('approval_notes')->nullable();

            $table->index('status', 'idx_stock_adjustments_status');
        });

        DB::statement('ALTER TABLE stock_adjustments ADD CONSTRAINT chk_adjustment_qty_positive CHECK (qty > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
