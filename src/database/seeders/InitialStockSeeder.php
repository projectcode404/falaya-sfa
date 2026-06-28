<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockBalance;
use App\Models\User;
use Illuminate\Database\Seeder;

class InitialStockSeeder extends Seeder
{
    public function run(): void
    {
        $salesman = User::where('email', 'salesman@falaya.test')->first();
        if (! $salesman) {
            return;
        }

        $products = Product::where('is_active', true)->get();

        foreach ($products as $product) {
            // Gudang GOOD stock
            StockBalance::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'holder_type' => 'WAREHOUSE',
                    'holder_id' => null,
                    'condition' => 'GOOD',
                ],
                ['qty' => 100]
            );

            // Salesman GOOD stock (simulasi sudah loading)
            StockBalance::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'holder_type' => 'SALESMAN',
                    'holder_id' => $salesman->id,
                    'condition' => 'GOOD',
                ],
                ['qty' => 30]
            );
        }
    }
}
