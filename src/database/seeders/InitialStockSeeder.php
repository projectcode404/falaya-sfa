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
            // Gudang GOOD stock -- satu-satunya yang aman diisi langsung lewat
            // StockBalance, karena tidak ada dokumen sumber yang merepresentasikan
            // "stok awal gudang" (berbeda dari stok salesman yang HARUS lewat
            // StockLoading agar tercatat di ledger dan terdeteksi Dashboard Admin).
            StockBalance::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'holder_type' => 'WAREHOUSE',
                    'holder_id' => null,
                    'condition' => 'GOOD',
                ],
                ['qty' => 100]
            );
        }
    }
}
