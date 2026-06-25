<?php

use App\Actions\Inventory\ApproveStockAdjustmentAction;
use App\Actions\Inventory\ApproveStockWriteoffAction;
use App\Actions\Inventory\CreateStockAdjustmentAction;
use App\Actions\Inventory\CreateStockWriteoffAction;
use App\Models\OperationalDate;
use App\Models\Product;
use App\Models\StockBalance;

function setupWriteoffTest(): array
{
    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);

    $admin = actingAsRole('ADMIN');

    $product = Product::create([
        'product_code' => 'KS-'.uniqid(),
        'product_name' => 'Keripik Writeoff Test',
        'unit' => 'pcs',
        'selling_price' => 5000,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    // Setup GOOD stock dan BAD stock
    StockBalance::create([
        'product_id' => $product->id,
        'holder_type' => 'WAREHOUSE',
        'holder_id' => null,
        'condition' => 'GOOD',
        'qty' => 100,
        'updated_at' => now(),
    ]);

    // Buat BAD stock via adjustment
    $adjustment = app(CreateStockAdjustmentAction::class)->execute([
        'product_id' => $product->id,
        'holder_type' => 'WAREHOUSE',
        'holder_id' => null,
        'qty' => 10,
        'reason' => 'RUSAK',
        'source_context' => 'WAREHOUSE_OPNAME',
    ]);

    actingAsRole('OWNER');
    app(ApproveStockAdjustmentAction::class)->execute($adjustment);

    // Kembali ke admin untuk test writeoff
    actingAsRole('ADMIN');

    return compact('product');
}

it('can create writeoff with pending status', function () {
    ['product' => $product] = setupWriteoffTest();

    $writeoff = app(CreateStockWriteoffAction::class)->execute(
        $product->id,
        5,
        'Barang rusak tidak bisa dijual'
    );

    expect($writeoff->status)->toBe('PENDING_APPROVAL')
        ->and($writeoff->document_number)->toStartWith('WO-');
});

it('owner can approve writeoff and remove bad stock permanently', function () {
    ['product' => $product] = setupWriteoffTest();

    $writeoff = app(CreateStockWriteoffAction::class)->execute(
        $product->id,
        5,
        'Barang rusak dimusnahkan'
    );

    actingAsRole('OWNER');
    app(ApproveStockWriteoffAction::class)->execute($writeoff);

    $badStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'WAREHOUSE')
        ->where('condition', 'BAD')->first();

    expect($writeoff->fresh()->status)->toBe('APPROVED')
        ->and((float) $badStock->qty)->toBe(5.0); // 10 - 5 = 5
});

it('cannot writeoff more than available bad stock', function () {
    ['product' => $product] = setupWriteoffTest();

    $writeoff = app(CreateStockWriteoffAction::class)->execute(
        $product->id,
        50, // BAD stock hanya 10
        'Terlalu banyak'
    );

    actingAsRole('OWNER');
    expect(fn () => app(ApproveStockWriteoffAction::class)->execute($writeoff))
        ->toThrow(RuntimeException::class);
});
