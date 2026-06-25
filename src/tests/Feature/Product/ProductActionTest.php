<?php

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\UpdateProductAction;
use App\Models\Product;
use Illuminate\Database\QueryException;

it('can create a product', function () {
    $admin = actingAsRole('ADMIN');
    $action = app(CreateProductAction::class);

    $product = $action->execute([
        'product_code' => 'KS-001',
        'product_name' => 'Keripik Singkong Original',
        'variant' => 'Original',
        'category' => 'Keripik',
        'unit' => 'pcs',
        'selling_price' => 5000,
    ]);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->product_code)->toBe('KS-001')
        ->and($product->selling_price)->toBe('5000.00')
        ->and($product->is_active)->toBeTrue()
        ->and($product->created_by)->toBe($admin->id);
});

it('can update a product', function () {
    $admin = actingAsRole('ADMIN');
    $create = app(CreateProductAction::class);
    $update = app(UpdateProductAction::class);

    $product = $create->execute([
        'product_code' => 'KS-002',
        'product_name' => 'Keripik Singkong Balado',
        'unit' => 'pcs',
        'selling_price' => 5000,
    ]);

    $updated = $update->execute($product, [
        'selling_price' => 6000,
        'variant' => 'Balado',
    ]);

    expect($updated->selling_price)->toBe('6000.00')
        ->and($updated->variant)->toBe('Balado')
        ->and($updated->updated_by)->toBe($admin->id);
});

it('product code must be unique', function () {
    actingAsRole('ADMIN');
    $action = app(CreateProductAction::class);

    $action->execute([
        'product_code' => 'KS-003',
        'product_name' => 'Produk A',
        'unit' => 'pcs',
        'selling_price' => 5000,
    ]);

    expect(fn () => $action->execute([
        'product_code' => 'KS-003',
        'product_name' => 'Produk B',
        'unit' => 'pcs',
        'selling_price' => 5000,
    ]))->toThrow(QueryException::class);
});
