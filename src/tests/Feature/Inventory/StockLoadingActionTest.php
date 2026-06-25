<?php

use App\Actions\Inventory\CancelStockLoadingAction;
use App\Actions\Inventory\CreateStockLoadingAction;
use App\Actions\Inventory\PostStockLoadingAction;
use App\Models\OperationalDate;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\User;

function setupInventoryTest(): array
{
    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);

    $admin = actingAsRole('ADMIN');

    $product = Product::create([
        'product_code' => 'KS-'.uniqid(),
        'product_name' => 'Keripik Singkong Original',
        'unit' => 'pcs',
        'selling_price' => 5000,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    // Set initial warehouse stock
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

    return compact('admin', 'product', 'salesman');
}

it('can create stock loading draft', function () {
    ['product' => $product, 'salesman' => $salesman] = setupInventoryTest();

    $loading = app(CreateStockLoadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 20]]
    );

    expect($loading->status)->toBe('DRAFT')
        ->and($loading->items)->toHaveCount(1)
        ->and($loading->document_number)->toStartWith('SL-');
});

it('can post stock loading and move stock', function () {
    ['product' => $product, 'salesman' => $salesman] = setupInventoryTest();

    $loading = app(CreateStockLoadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 20]]
    );

    app(PostStockLoadingAction::class)->execute($loading);

    $warehouseStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'WAREHOUSE')->where('condition', 'GOOD')->first();

    $salesmanStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->where('condition', 'GOOD')->first();

    expect($loading->fresh()->status)->toBe('POSTED')
        ->and((float) $warehouseStock->qty)->toBe(80.0)
        ->and((float) $salesmanStock->qty)->toBe(20.0);
});

it('cannot post loading when warehouse stock insufficient', function () {
    ['product' => $product, 'salesman' => $salesman] = setupInventoryTest();

    $loading = app(CreateStockLoadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 150]] // melebihi stok 100
    );

    expect(fn () => app(PostStockLoadingAction::class)->execute($loading))
        ->toThrow(RuntimeException::class);
});

it('can cancel stock loading draft', function () {
    ['product' => $product, 'salesman' => $salesman] = setupInventoryTest();

    $loading = app(CreateStockLoadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 10]]
    );

    app(CancelStockLoadingAction::class)->execute($loading, 'Salesman tidak hadir');

    expect($loading->fresh()->status)->toBe('CANCELLED')
        ->and($loading->fresh()->cancel_reason)->toBe('Salesman tidak hadir');
});

it('re-loading is allowed for same salesman same day', function () {
    ['product' => $product, 'salesman' => $salesman] = setupInventoryTest();

    $loading1 = app(CreateStockLoadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 20]]
    );
    app(PostStockLoadingAction::class)->execute($loading1);

    $loading2 = app(CreateStockLoadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 30]]
    );
    app(PostStockLoadingAction::class)->execute($loading2);

    $salesmanStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->where('condition', 'GOOD')->first();

    expect((float) $salesmanStock->qty)->toBe(50.0);
});
