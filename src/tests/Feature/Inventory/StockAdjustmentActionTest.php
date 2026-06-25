<?php

use App\Actions\Inventory\ApproveStockAdjustmentAction;
use App\Actions\Inventory\CreateStockAdjustmentAction;
use App\Actions\Inventory\RejectStockAdjustmentAction;
use App\Models\OperationalDate;
use App\Models\Product;
use App\Models\StockBalance;

function setupAdjustmentTest(): array
{
    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);

    $admin = actingAsRole('ADMIN');

    $product = Product::create([
        'product_code' => 'KS-'.uniqid(),
        'product_name' => 'Keripik Test',
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

    return compact('admin', 'product');
}

it('can create stock adjustment with pending status', function () {
    ['product' => $product] = setupAdjustmentTest();

    $adjustment = app(CreateStockAdjustmentAction::class)->execute([
        'product_id' => $product->id,
        'holder_type' => 'WAREHOUSE',
        'holder_id' => null,
        'qty' => 5,
        'reason' => 'RUSAK',
        'source_context' => 'WAREHOUSE_OPNAME',
    ]);

    expect($adjustment->status)->toBe('PENDING_APPROVAL')
        ->and($adjustment->document_number)->toStartWith('SA-');
});

it('owner can approve adjustment and move stock to warehouse bad', function () {
    ['product' => $product] = setupAdjustmentTest();

    $adjustment = app(CreateStockAdjustmentAction::class)->execute([
        'product_id' => $product->id,
        'holder_type' => 'WAREHOUSE',
        'holder_id' => null,
        'qty' => 5,
        'reason' => 'RUSAK',
        'source_context' => 'WAREHOUSE_OPNAME',
    ]);

    $owner = actingAsRole('OWNER');
    app(ApproveStockAdjustmentAction::class)->execute($adjustment);

    $goodStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'WAREHOUSE')->where('condition', 'GOOD')->first();

    $badStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'WAREHOUSE')->where('condition', 'BAD')->first();

    expect($adjustment->fresh()->status)->toBe('APPROVED')
        ->and((float) $goodStock->qty)->toBe(95.0)
        ->and((float) $badStock->qty)->toBe(5.0);
});

it('owner can reject adjustment', function () {
    ['product' => $product] = setupAdjustmentTest();

    $adjustment = app(CreateStockAdjustmentAction::class)->execute([
        'product_id' => $product->id,
        'holder_type' => 'WAREHOUSE',
        'holder_id' => null,
        'qty' => 5,
        'reason' => 'EXPIRED',
        'source_context' => 'WAREHOUSE_OPNAME',
    ]);

    actingAsRole('OWNER');
    app(RejectStockAdjustmentAction::class)->execute($adjustment, 'Data tidak valid');

    expect($adjustment->fresh()->status)->toBe('REJECTED')
        ->and($adjustment->fresh()->approval_notes)->toBe('Data tidak valid');
});
