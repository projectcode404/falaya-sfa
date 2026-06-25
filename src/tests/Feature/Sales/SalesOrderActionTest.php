<?php

use App\Actions\Inventory\CreateStockLoadingAction;
use App\Actions\Inventory\PostStockLoadingAction;
use App\Actions\Sales\CancelSalesOrderAction;
use App\Actions\Sales\CreateSalesOrderAction;
use App\Actions\Sales\PostSalesOrderAction;
use App\Actions\Sales\VoidSalesOrderAction;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OperationalDate;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\VisitPlan;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Artisan;

function setupSalesTest(): array
{
    Artisan::call('db:seed', ['--class' => SettingsSeeder::class]);

    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);

    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Area Sales Test',
        'area_code' => 'AST-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $product = Product::create([
        'product_code' => 'KS-'.uniqid(),
        'product_name' => 'Keripik Test',
        'unit' => 'pcs',
        'selling_price' => 5000,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    $salesman = User::factory()->create(['role' => 'SALESMAN']);
    $salesman->assignRole('SALESMAN');

    StockBalance::create([
        'product_id' => $product->id,
        'holder_type' => 'WAREHOUSE',
        'holder_id' => null,
        'condition' => 'GOOD',
        'qty' => 100,
        'updated_at' => now(),
    ]);

    $loading = app(CreateStockLoadingAction::class)->execute(
        $salesman->id,
        [['product_id' => $product->id, 'qty' => 50]]
    );
    app(PostStockLoadingAction::class)->execute($loading);

    $cashCustomer = Customer::create([
        'customer_code' => 'CASH-'.uniqid(),
        'customer_name' => 'Warung Cash Test',
        'address' => 'Jl. Test No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $creditCustomer = Customer::create([
        'customer_code' => 'CREDIT-'.uniqid(),
        'customer_name' => 'Toko Credit Test',
        'address' => 'Jl. Test No. 2',
        'area_id' => $area->id,
        'customer_type' => 'CREDIT',
        'status' => 'ACTIVE',
        'credit_limit' => 500000,
        'credit_term_days' => 14,
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $cashVisitPlan = VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $cashCustomer->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => false,
        'area_id_snapshot' => $area->id,
        'status' => 'IN_PROGRESS',
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $creditVisitPlan = VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $creditCustomer->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => false,
        'area_id_snapshot' => $area->id,
        'status' => 'IN_PROGRESS',
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    return compact('admin', 'salesman', 'product', 'cashCustomer', 'creditCustomer', 'cashVisitPlan', 'creditVisitPlan');
}

it('can create and post cash sales order', function () {
    ['salesman' => $salesman, 'product' => $product, 'cashCustomer' => $customer, 'cashVisitPlan' => $visitPlan] = setupSalesTest();

    actingAsRole('SALESMAN');

    $so = app(CreateSalesOrderAction::class)->execute(
        $visitPlan->id,
        $customer->id,
        $salesman->id,
        'CASH',
        [['product_id' => $product->id, 'qty' => 10, 'unit_price' => 5000]]
    );

    expect($so->status)->toBe('DRAFT')
        ->and((float) $so->total_amount)->toBe(50000.0);

    app(PostSalesOrderAction::class)->execute($so);

    $salesmanStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->where('condition', 'GOOD')->first();

    expect($so->fresh()->status)->toBe('POSTED')
        ->and((float) $salesmanStock->qty)->toBe(40.0)
        ->and($visitPlan->fresh()->status)->toBe('COMPLETED');
});

it('can create and post credit sales order and generate invoice', function () {
    ['salesman' => $salesman, 'product' => $product, 'creditCustomer' => $customer, 'creditVisitPlan' => $visitPlan] = setupSalesTest();

    actingAsRole('SALESMAN');

    $so = app(CreateSalesOrderAction::class)->execute(
        $visitPlan->id,
        $customer->id,
        $salesman->id,
        'CREDIT',
        [['product_id' => $product->id, 'qty' => 5, 'unit_price' => 5000]],
        'Pak Budi'
    );

    app(PostSalesOrderAction::class)->execute($so);

    $invoice = Invoice::where('sales_order_id', $so->id)->first();

    expect($so->fresh()->status)->toBe('POSTED')
        ->and($invoice)->not->toBeNull()
        ->and($invoice->invoice_number)->toStartWith('INV-')
        ->and((float) $invoice->total_amount)->toBe(25000.0)
        ->and((float) $invoice->remaining_amount)->toBe(25000.0)
        ->and($invoice->status)->toBe('UNPAID');
});

it('cannot post credit sales order exceeding credit limit', function () {
    ['salesman' => $salesman, 'product' => $product, 'creditCustomer' => $customer, 'creditVisitPlan' => $visitPlan] = setupSalesTest();

    actingAsRole('SALESMAN');

    $so = app(CreateSalesOrderAction::class)->execute(
        $visitPlan->id,
        $customer->id,
        $salesman->id,
        'CREDIT',
        [['product_id' => $product->id, 'qty' => 200, 'unit_price' => 5000]]
    );

    expect(fn () => app(PostSalesOrderAction::class)->execute($so))
        ->toThrow(RuntimeException::class, 'Melebihi credit limit');
});

it('cannot post sales order with insufficient stock', function () {
    ['salesman' => $salesman, 'product' => $product, 'cashCustomer' => $customer, 'cashVisitPlan' => $visitPlan] = setupSalesTest();

    actingAsRole('SALESMAN');

    $so = app(CreateSalesOrderAction::class)->execute(
        $visitPlan->id,
        $customer->id,
        $salesman->id,
        'CASH',
        [['product_id' => $product->id, 'qty' => 100, 'unit_price' => 5000]]
    );

    expect(fn () => app(PostSalesOrderAction::class)->execute($so))
        ->toThrow(RuntimeException::class);
});

it('can cancel draft sales order', function () {
    ['salesman' => $salesman, 'product' => $product, 'cashCustomer' => $customer, 'cashVisitPlan' => $visitPlan] = setupSalesTest();

    actingAsRole('SALESMAN');

    $so = app(CreateSalesOrderAction::class)->execute(
        $visitPlan->id,
        $customer->id,
        $salesman->id,
        'CASH',
        [['product_id' => $product->id, 'qty' => 10, 'unit_price' => 5000]]
    );

    app(CancelSalesOrderAction::class)->execute($so, 'Customer batal pesan');

    expect($so->fresh()->status)->toBe('CANCELLED')
        ->and($so->fresh()->cancel_reason)->toBe('Customer batal pesan');
});

it('can cancel posted sales order and reverse stock', function () {
    ['salesman' => $salesman, 'product' => $product, 'cashCustomer' => $customer, 'cashVisitPlan' => $visitPlan] = setupSalesTest();

    actingAsRole('SALESMAN');

    $so = app(CreateSalesOrderAction::class)->execute(
        $visitPlan->id,
        $customer->id,
        $salesman->id,
        'CASH',
        [['product_id' => $product->id, 'qty' => 10, 'unit_price' => 5000]]
    );
    app(PostSalesOrderAction::class)->execute($so);
    app(CancelSalesOrderAction::class)->execute($so, 'Dibatalkan');

    $salesmanStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->where('condition', 'GOOD')->first();

    expect($so->fresh()->status)->toBe('CANCELLED')
        ->and((float) $salesmanStock->qty)->toBe(50.0);
});

it('admin can void posted sales order', function () {
    ['salesman' => $salesman, 'product' => $product, 'cashCustomer' => $customer, 'cashVisitPlan' => $visitPlan] = setupSalesTest();

    actingAsRole('SALESMAN');
    $so = app(CreateSalesOrderAction::class)->execute(
        $visitPlan->id,
        $customer->id,
        $salesman->id,
        'CASH',
        [['product_id' => $product->id, 'qty' => 10, 'unit_price' => 5000]]
    );
    app(PostSalesOrderAction::class)->execute($so);

    actingAsRole('ADMIN');
    app(VoidSalesOrderAction::class)->execute($so, 'Kesalahan input');

    expect($so->fresh()->status)->toBe('VOID')
        ->and($so->fresh()->void_reason)->toBe('Kesalahan input');
});
