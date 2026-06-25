<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_unloading_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_unloading_id')->constrained('stock_unloadings');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('qty', 15, 3);
            // TIDAK ADA kolom condition -- selalu GOOD (PRD Bagian 6.4)

            $table->index('stock_unloading_id', 'idx_stock_unloading_items_unloading');
        });

        DB::statement('ALTER TABLE stock_unloading_items ADD CONSTRAINT chk_unloading_item_qty_positive CHECK (qty > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_unloading_items');
    }
};
