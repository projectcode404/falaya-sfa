<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_return_id')->constrained('customer_returns');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('qty', 15, 3);
            $table->decimal('unit_price', 15, 2);
        });

        DB::statement('ALTER TABLE customer_return_items ADD CONSTRAINT chk_return_item_qty_positive CHECK (qty > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_return_items');
    }
};
