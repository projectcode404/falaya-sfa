<?php

use App\Actions\CustomerReturn\ApproveCustomerReturnAction;
use App\Actions\CustomerReturn\CreateCustomerReturnAction;
use App\Actions\Inventory\CreateStockLoadingAction;
use App\Actions\Inventory\PostStockLoadingAction;
use App\Actions\Sales\CreateSalesOrderAction;
use App\Actions\Sales\PostSalesOrderAction;
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

function setupReturnTest(): array
{
    Artisan::call('db:seed', ['--class' => SettingsSeeder::class]);

    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);

    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Area Return Test',
        'area_code' => 'ART-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $product = Product::create([
        'product_code' => 'KS-'.uniqid(),
        'product_name' => 'Keripik Return Test',
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

    $customer = Customer::create([
        'customer_code' => 'CREDIT-'.uniqid(),
        'customer_name' => 'Toko Return Test',
        'address' => 'Jl. Test No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CREDIT',
        'status' => 'ACTIVE',
        'credit_limit' => 1000000,
        'credit_term_days' => 14,
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $visitPlan = VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => false,
        'area_id_snapshot' => $area->id,
        'status' => 'IN_PROGRESS',
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    // Buat SO CREDIT dan invoice
    actingAsRole('SALESMAN');
    $so = app(CreateSalesOrderAction::class)->execute(
        $visitPlan->id,
        $customer->id,
        $salesman->id,
        'CREDIT',
        [['product_id' => $product->id, 'qty' => 10, 'unit_price' => 5000]],
        'Pak Budi'
    );
    app(PostSalesOrderAction::class)->execute($so);

    $invoice = Invoice::where('sales_order_id', $so->id)->first();

    return compact('admin', 'salesman', 'product', 'customer', 'invoice');
}

it('can create customer return with pending status', function () {
    ['salesman' => $salesman, 'product' => $product, 'customer' => $customer, 'invoice' => $invoice] = setupReturnTest();

    actingAsRole('SALESMAN');

    $return = app(CreateCustomerReturnAction::class)->execute([
        'invoice_id' => $invoice->id,
        'customer_id' => $customer->id,
        'salesman_id' => $salesman->id,
        'reason' => 'RUSAK',
        'refund_type' => 'CREDIT_NOTE',
        'items' => [
            ['product_id' => $product->id, 'qty' => 2, 'unit_price' => 5000],
        ],
    ]);

    expect($return->status)->toBe('PENDING_APPROVAL')
        ->and($return->document_number)->toStartWith('CR-')
        ->and((float) $return->total_amount)->toBe(10000.0);
});

it('owner can approve customer return and move stock to warehouse bad', function () {
    ['salesman' => $salesman, 'product' => $product, 'customer' => $customer, 'invoice' => $invoice] = setupReturnTest();

    actingAsRole('SALESMAN');
    $return = app(CreateCustomerReturnAction::class)->execute([
        'invoice_id' => $invoice->id,
        'customer_id' => $customer->id,
        'salesman_id' => $salesman->id,
        'reason' => 'RUSAK',
        'refund_type' => 'CREDIT_NOTE',
        'items' => [
            ['product_id' => $product->id, 'qty' => 2, 'unit_price' => 5000],
        ],
    ]);

    actingAsRole('OWNER');
    app(ApproveCustomerReturnAction::class)->execute($return);

    $badStock = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'WAREHOUSE')
        ->where('condition', 'BAD')->first();

    $updatedInvoice = $invoice->fresh();

    expect($return->fresh()->status)->toBe('APPROVED')
        ->and((float) $badStock->qty)->toBe(2.0)
        ->and((float) $updatedInvoice->paid_amount)->toBe(10000.0)
        ->and((float) $updatedInvoice->remaining_amount)->toBe(40000.0);
});
