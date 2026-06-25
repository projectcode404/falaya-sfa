<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    public function execute(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            return Product::create([
                'product_code' => $data['product_code'],
                'product_name' => $data['product_name'],
                'variant' => $data['variant'] ?? null,
                'category' => $data['category'] ?? null,
                'unit' => $data['unit'],
                'selling_price' => $data['selling_price'],
                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);
        });
    }
}
