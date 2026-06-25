<?php

use App\Actions\Inventory\CreateStockLoadingAction;
use App\Actions\Inventory\CreateStockUnloadingAction;
use App\Actions\Inventory\PostStockLoadingAction;
use App\Actions\Inventory\PostStockUnloadingAction;
use App\Models\OperationalDate;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\User;

function setupUnloadingTest(): array
{
    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);

    $admin = actingAsRole('ADMIN');

    $product = Product::create([
        'product_code' => 'KS-'.uniqid(),
        'product_name' => 'Keripik Singkong Balado',
        'unit' => 'pcs',
        'selling_price' => 5000,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    StockBalance::create([
        'product_id' => $product->id,
        'holder_type' => 'WAREHOUSE',
        'holder_id' => null,
        'condition' => 'GOOD',
        'qty' => 100,
        'updated_at' => now(),
    ]);

    $salesman = User::factory()->create(['role' => 'SALESMAN']);
    $salesman->assignRole('SALESMAN');

    // Loading dulu ke salesman
    $loading = app(CreateStockLoadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 50]]
    );
    app(PostStockLoadingAction::class)->execute($loading);

    return compact('admin', 'product', 'salesman');
}

it('can post stock unloading and return stock to warehouse', function () {
    ['product' => $product, 'salesman' => $salesman] = setupUnloadingTest();

    $unloading = app(CreateStockUnloadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 30]]
    );

    app(PostStockUnloadingAction::class)->execute($unloading);

    $warehouseStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'WAREHOUSE')->where('condition', 'GOOD')->first();

    $salesmanStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->where('condition', 'GOOD')->first();

    expect($unloading->fresh()->status)->toBe('POSTED')
        ->and((float) $warehouseStock->qty)->toBe(80.0)
        ->and((float) $salesmanStock->qty)->toBe(20.0);
});

it('unloading always returns GOOD condition stock', function () {
    ['product' => $product, 'salesman' => $salesman] = setupUnloadingTest();

    $unloading = app(CreateStockUnloadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 10]]
    );
    app(PostStockUnloadingAction::class)->execute($unloading);

    // Tidak boleh ada BAD stock di salesman
    $badStock = StockBalance::where('holder_type', 'SALESMAN')
        ->where('condition', 'BAD')->first();

    expect($badStock)->toBeNull();
});

it('cannot unload more than salesman stock', function () {
    ['product' => $product, 'salesman' => $salesman] = setupUnloadingTest();

    $unloading = app(CreateStockUnloadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 100]] // salesman hanya punya 50
    );

    expect(fn () => app(PostStockUnloadingAction::class)->execute($unloading))
        ->toThrow(RuntimeException::class);
});
