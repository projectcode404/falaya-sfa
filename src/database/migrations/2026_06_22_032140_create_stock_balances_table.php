<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->string('holder_type', 20);
            $table->unsignedBigInteger('holder_id')->nullable();
            $table->string('condition', 10);
            $table->decimal('qty', 15, 3)->default(0);
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(
                ['product_id', 'holder_type', 'holder_id', 'condition'],
                'uq_stock_balance'
            );
        });

        DB::statement('ALTER TABLE stock_balances ADD CONSTRAINT chk_qty_non_negative CHECK (qty >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
