<?php

namespace Database\Seeders;

use App\Actions\Inventory\CreateStockLoadingAction;
use App\Actions\Inventory\PostStockLoadingAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class StockLoadingSeeder extends Seeder
{
    public function run(): void
    {
        $salesman = User::where('email', 'salesman@falaya.test')->first();

        if (! $salesman) {
            return;
        }

        $products = Product::where('is_active', true)->get();

        if ($products->isEmpty()) {
            return;
        }

        // Action ini memanggil auth()->id() untuk created_by/posted_by --
        // login sementara sebagai admin (atau salesman sendiri jika tidak ada
        // admin) supaya kolom audit terisi benar, lalu logout lagi di akhir
        // agar tidak mengganggu state Auth seeder lain yang berjalan setelahnya.
        $previousUser = Auth::user();
        $actor = User::role('ADMIN')->first() ?? $salesman;
        Auth::login($actor);

        $items = $products->map(fn ($product) => [
            'product_id' => $product->id,
            'qty' => 30,
        ])->values()->toArray();

        $loading = app(CreateStockLoadingAction::class)->execute($salesman->id, $items);
        app(PostStockLoadingAction::class)->execute($loading);

        if ($previousUser) {
            Auth::login($previousUser);
        } else {
            Auth::logout();
        }
    }
}
