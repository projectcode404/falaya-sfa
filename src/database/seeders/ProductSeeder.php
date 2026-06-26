<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'product_code' => 'FLY-ORI-250',
                'product_name' => 'Keripik Singkong Falaya',
                'variant' => 'Original 250gr',
                'category' => 'Keripik Singkong',
                'unit' => 'pcs',
                'selling_price' => 15000,
            ],
            [
                'product_code' => 'FLY-BAL-250',
                'product_name' => 'Keripik Singkong Falaya',
                'variant' => 'Balado 250gr',
                'category' => 'Keripik Singkong',
                'unit' => 'pcs',
                'selling_price' => 15000,
            ],
            [
                'product_code' => 'FLY-PED-250',
                'product_name' => 'Keripik Singkong Falaya',
                'variant' => 'Pedas 250gr',
                'category' => 'Keripik Singkong',
                'unit' => 'pcs',
                'selling_price' => 15000,
            ],
            [
                'product_code' => 'FLY-ORI-500',
                'product_name' => 'Keripik Singkong Falaya',
                'variant' => 'Original 500gr',
                'category' => 'Keripik Singkong',
                'unit' => 'pcs',
                'selling_price' => 28000,
            ],
            [
                'product_code' => 'FLY-BAL-500',
                'product_name' => 'Keripik Singkong Falaya',
                'variant' => 'Balado 500gr',
                'category' => 'Keripik Singkong',
                'unit' => 'pcs',
                'selling_price' => 28000,
            ],
            [
                'product_code' => 'FLY-MIX-BOX',
                'product_name' => 'Keripik Singkong Falaya',
                'variant' => 'Mix Box (isi 12 pcs)',
                'category' => 'Keripik Singkong',
                'unit' => 'box',
                'selling_price' => 160000,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['product_code' => $product['product_code']],
                array_merge($product, [
                    'is_active' => true,
                    'created_by' => 1,
                    'created_at' => now(),
                ])
            );
        }
    }
}
