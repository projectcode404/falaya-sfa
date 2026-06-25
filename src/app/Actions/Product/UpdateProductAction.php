<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateProductAction
{
    public function execute(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update([
                'product_name' => $data['product_name'] ?? $product->product_name,
                'variant' => $data['variant'] ?? $product->variant,
                'category' => $data['category'] ?? $product->category,
                'unit' => $data['unit'] ?? $product->unit,
                'selling_price' => $data['selling_price'] ?? $product->selling_price,
                'is_active' => $data['is_active'] ?? $product->is_active,
                'updated_by' => auth()->id(),
            ]);

            return $product->fresh();
        });
    }
}
