<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_writeoffs', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 30)->unique();

            $table->foreignId('product_id')->constrained('products');
            $table->decimal('qty', 15, 3);
            $table->text('reason');
            // Selalu dari Gudang-BAD -- tidak ada kolom holder_type/holder_id
            // (PRD Bagian 6.6)

            $table->string('status', 20)->default('PENDING_APPROVAL');

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('approval_notes')->nullable();

            $table->index('status', 'idx_stock_writeoffs_status');
        });

        DB::statement('ALTER TABLE stock_writeoffs ADD CONSTRAINT chk_writeoff_qty_positive CHECK (qty > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_writeoffs');
    }
};
