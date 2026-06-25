<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->string('holder_type', 20);
            $table->unsignedBigInteger('holder_id')->nullable();
            $table->string('condition', 10);
            $table->decimal('qty', 15, 3);
            $table->date('operational_date');
            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_id');
            $table->foreignId('reference_ledger_id')->nullable()->constrained('stock_ledgers');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            // Tidak ada updated_at -- append-only
        });

        DB::statement('CREATE INDEX idx_stock_ledgers_balance_calc ON stock_ledgers(product_id, holder_type, holder_id, condition)');
        DB::statement('CREATE INDEX idx_stock_ledgers_source ON stock_ledgers(source_type, source_id)');
        DB::statement('CREATE INDEX idx_stock_ledgers_date ON stock_ledgers(operational_date)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
