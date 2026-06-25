<?php

use App\Actions\Collection\CreatePaymentAction;
use App\Actions\Collection\PostPaymentAction;
use App\Actions\Collection\ProcessCashReconciliationAction;
use App\Actions\Collection\VoidPaymentAction;
use App\Actions\Inventory\CreateStockLoadingAction;
use App\Actions\Inventory\PostStockLoadingAction;
use App\Actions\Sales\CreateSalesOrderAction;
use App\Actions\Sales\PostSalesOrderAction;
use App\DomainServices\PaymentAllocationService;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OperationalDate;
use App\Models\PaymentReceipt;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\VisitPlan;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Artisan;

function setupCollectionTest(): array
{
    Artisan::call('db:seed', ['--class' => SettingsSeeder::class]);

    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);

    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Area Collection Test',
        'area_code' => 'ACT-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $product = Product::create([
        'product_code' => 'KS-'.uniqid(),
        'product_name' => 'Keripik Collection Test',
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
        'customer_name' => 'Toko Collection Test',
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

    // Buat invoice via SO CREDIT
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

    return compact('admin', 'salesman', 'product', 'customer', 'invoice', 'visitPlan');
}

it('payment allocation service allocates fifo correctly', function () {
    ['customer' => $customer, 'invoice' => $invoice] = setupCollectionTest();

    $service = app(PaymentAllocationService::class);
    $allocations = $service->allocateFifo($customer->id, 30000);

    expect($allocations)->toHaveKey($invoice->id)
        ->and($allocations[$invoice->id])->toBe(30000.0);
});

it('can create payment with allocations', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'invoice' => $invoice, 'visitPlan' => $visitPlan] = setupCollectionTest();

    actingAsRole('SALESMAN');

    $payment = app(CreatePaymentAction::class)->execute(
        $customer->id,
        30000,
        'CASH',
        [$invoice->id => 30000],
        $visitPlan->id
    );

    expect($payment->status)->toBe('DRAFT')
        ->and($payment->payment_number)->toStartWith('PAY-')
        ->and($payment->allocations)->toHaveCount(1);
});

it('can post payment and update invoice', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'invoice' => $invoice, 'visitPlan' => $visitPlan] = setupCollectionTest();

    actingAsRole('SALESMAN');

    $payment = app(CreatePaymentAction::class)->execute(
        $customer->id,
        30000,
        'CASH',
        [$invoice->id => 30000],
        $visitPlan->id
    );

    app(PostPaymentAction::class)->execute($payment);

    $updatedInvoice = $invoice->fresh();
    $receipt = PaymentReceipt::where('payment_id', $payment->id)->first();

    expect($payment->fresh()->status)->toBe('POSTED')
        ->and((float) $updatedInvoice->paid_amount)->toBe(30000.0)
        ->and((float) $updatedInvoice->remaining_amount)->toBe(20000.0)
        ->and($updatedInvoice->status)->toBe('PARTIAL')
        ->and($receipt)->not->toBeNull()
        ->and($receipt->receipt_number)->toStartWith('RCP-');
});

it('can fully pay invoice', function () {
    ['customer' => $customer, 'invoice' => $invoice, 'visitPlan' => $visitPlan] = setupCollectionTest();

    actingAsRole('SALESMAN');

    $payment = app(CreatePaymentAction::class)->execute(
        $customer->id,
        50000,
        'CASH',
        [$invoice->id => 50000],
        $visitPlan->id
    );

    app(PostPaymentAction::class)->execute($payment);

    expect($invoice->fresh()->status)->toBe('PAID')
        ->and((float) $invoice->fresh()->remaining_amount)->toBe(0.0);
});

it('cannot post payment when allocation sum does not match', function () {
    ['customer' => $customer, 'invoice' => $invoice] = setupCollectionTest();

    actingAsRole('SALESMAN');

    $payment = app(CreatePaymentAction::class)->execute(
        $customer->id,
        30000,
        'CASH',
        [$invoice->id => 20000] // tidak sama dengan 30000
    );

    expect(fn () => app(PostPaymentAction::class)->execute($payment))
        ->toThrow(RuntimeException::class);
});

it('owner can void payment and reverse invoice', function () {
    ['customer' => $customer, 'invoice' => $invoice, 'visitPlan' => $visitPlan] = setupCollectionTest();

    actingAsRole('SALESMAN');
    $payment = app(CreatePaymentAction::class)->execute(
        $customer->id,
        30000,
        'CASH',
        [$invoice->id => 30000],
        $visitPlan->id
    );
    app(PostPaymentAction::class)->execute($payment);

    actingAsRole('OWNER');
    app(VoidPaymentAction::class)->execute($payment, 'Pembayaran salah');

    expect($payment->fresh()->status)->toBe('VOID')
        ->and((float) $invoice->fresh()->paid_amount)->toBe(0.0)
        ->and((float) $invoice->fresh()->remaining_amount)->toBe(50000.0)
        ->and($invoice->fresh()->status)->toBe('UNPAID');
});

it('can process cash reconciliation', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'invoice' => $invoice, 'visitPlan' => $visitPlan] = setupCollectionTest();

    test()->actingAs($salesman);
    $payment = app(CreatePaymentAction::class)->execute(
        $customer->id,
        50000,
        'CASH',
        [$invoice->id => 50000],
        $visitPlan->id
    );
    app(PostPaymentAction::class)->execute($payment);

    actingAsRole('ADMIN');
    $reconciliation = app(ProcessCashReconciliationAction::class)->execute(
        $salesman->id,
        50000 // actual received sama dengan system
    );

    expect($reconciliation->status)->toBe('RECONCILED')
        ->and((float) $reconciliation->difference)->toBe(0.0);
});

it('reconciliation shows discrepancy when amounts differ', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'invoice' => $invoice, 'visitPlan' => $visitPlan] = setupCollectionTest();

    test()->actingAs($salesman);
    $payment = app(CreatePaymentAction::class)->execute(
        $customer->id,
        50000,
        'CASH',
        [$invoice->id => 50000],
        $visitPlan->id
    );
    app(PostPaymentAction::class)->execute($payment);

    actingAsRole('ADMIN');
    $reconciliation = app(ProcessCashReconciliationAction::class)->execute(
        $salesman->id,
        40000 // kurang 10000 dari system
    );

    expect($reconciliation->status)->toBe('DISCREPANCY')
        ->and((float) $reconciliation->difference)->toBe(-10000.0);
});
