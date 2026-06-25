<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('qty', 15, 3);
            $table->decimal('unit_price', 15, 2); // snapshot harga saat transaksi
            $table->decimal('subtotal', 15, 2);
        });

        DB::statement('ALTER TABLE sales_order_items ADD CONSTRAINT chk_so_item_qty_positive CHECK (qty > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
