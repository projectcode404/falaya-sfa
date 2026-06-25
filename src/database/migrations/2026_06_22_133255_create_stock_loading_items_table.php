<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_loading_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_loading_id')->constrained('stock_loadings');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('qty', 15, 3);

            $table->index('stock_loading_id', 'idx_stock_loading_items_loading');
        });

        DB::statement('ALTER TABLE stock_loading_items ADD CONSTRAINT chk_loading_item_qty_positive CHECK (qty > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_loading_items');
    }
};
