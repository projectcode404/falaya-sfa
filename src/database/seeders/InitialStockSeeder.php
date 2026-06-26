<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockBalance;
use Illuminate\Database\Seeder;

class InitialStockSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('is_active', true)->get();

        foreach ($products as $product) {
            StockBalance::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'holder_type' => 'WAREHOUSE',
                    'holder_id' => null,
                    'condition' => 'GOOD',
                ],
                ['qty' => 500]
            );
        }
    }
}
