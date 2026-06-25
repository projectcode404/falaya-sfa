<?php

use App\Models\Area;
use App\Models\CollectionTask;
use App\Models\Customer;
use App\Models\DocumentSequence;
use App\Models\Invoice;
use App\Models\OperationalDate;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\VisitPlan;
use App\Models\VisitRealization;

beforeEach(function () {
    if (OperationalDate::count() === 0) {
        OperationalDate::create([
            'current_date_value' => now()->toDateString(),
            'is_closing_in_progress' => false,
            'updated_at' => now(),
        ]);
    }
});

function makeCollectionFixture(): array
{
    $salesman = User::factory()->create();
    $salesman->assignRole('SALESMAN');

    $area = Area::create([
        'area_name' => 'Area Col',
        'area_code' => 'COL-'.uniqid(),
        'is_active' => true,
        'created_by' => $salesman->id,
    ]);

    $customer = Customer::create([
        'customer_code' => 'CCOL-'.uniqid(),
        'customer_name' => 'Outlet Piutang',
        'address' => 'Jl. Piutang No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CREDIT',
        'status' => 'ACTIVE',
        'credit_limit' => 5000000,
        'credit_term_days' => 14,
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $product = Product::create([
        'product_code' => 'PRD-COL-'.uniqid(),
        'product_name' => 'Keripik Test',
        'unit' => 'pcs',
        'selling_price' => 5000,
        'is_active' => true,
        'created_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $visitPlan = VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => true,
        'area_id_snapshot' => $area->id,
        'status' => 'IN_PROGRESS',
        'created_at' => now(),
    ]);

    VisitRealization::create([
        'visit_plan_id' => $visitPlan->id,
        'checkin_at' => now(),
        'gps_unavailable' => true,
        'gps_low_accuracy' => false,
        'created_at' => now(),
    ]);

    // Buat SalesOrder nyata agar FK invoices.sales_order_id valid
    $salesOrder = SalesOrder::create([
        'document_number' => 'SO-COL-'.uniqid(),
        'visit_plan_id' => $visitPlan->id,
        'customer_id' => $customer->id,
        'salesman_id' => $salesman->id,
        'operational_date' => now()->toDateString(),
        'payment_type' => 'CREDIT',
        'subtotal' => 500000,
        'total_amount' => 500000,
        'status' => 'POSTED',
        'created_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'INV-COL-'.uniqid(),
        'sales_order_id' => $salesOrder->id,
        'customer_id' => $customer->id,
        'salesman_id' => $salesman->id,
        'customer_name_snapshot' => $customer->customer_name,
        'customer_address_snapshot' => $customer->address,
        'receiver_name' => 'Pak Test',
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
        'credit_term_days_snapshot' => 14,
        'total_amount' => 500000,
        'paid_amount' => 0,
        'remaining_amount' => 500000,
        'status' => 'UNPAID',
        'created_at' => now(),
    ]);

    $collectionTask = CollectionTask::create([
        'visit_plan_id' => $visitPlan->id,
        'customer_id' => $customer->id,
        'salesman_id' => $salesman->id,
        'operational_date' => now()->toDateString(),
        'total_outstanding_snapshot' => 500000,
        'priority' => 'NORMAL',
        'status' => 'PLANNED',
        'created_at' => now(),
    ]);

    // DocumentSequence untuk PAY dan RCP agar DocumentNumberService bisa generate
    DocumentSequence::firstOrCreate(
        ['document_type' => 'PAY', 'operational_date' => now()->toDateString()],
        ['last_number' => 0]
    );
    DocumentSequence::firstOrCreate(
        ['document_type' => 'RCP', 'operational_date' => now()->toDateString()],
        ['last_number' => 0]
    );

    return compact('salesman', 'customer', 'visitPlan', 'invoice', 'collectionTask');
}

it('salesman dapat mencatat pembayaran cash dan invoice terlunasi', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'visitPlan' => $visitPlan, 'invoice' => $invoice] = makeCollectionFixture();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/collection/payment', [
        'customer_id' => $customer->id,
        'visit_plan_id' => $visitPlan->id,
        'total_amount' => 500000,
        'allocations' => [
            ['invoice_id' => $invoice->id, 'amount' => 500000],
        ],
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'Pembayaran berhasil dicatat.']);

    expect($invoice->fresh()->status)->toBe('PAID');
    expect((float) $invoice->fresh()->remaining_amount)->toBe(0.0);
});

it('pembayaran parsial mengubah status invoice menjadi PARTIAL', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'visitPlan' => $visitPlan, 'invoice' => $invoice] = makeCollectionFixture();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/collection/payment', [
        'customer_id' => $customer->id,
        'visit_plan_id' => $visitPlan->id,
        'total_amount' => 200000,
        'allocations' => [
            ['invoice_id' => $invoice->id, 'amount' => 200000],
        ],
    ]);

    $response->assertStatus(201);
    expect($invoice->fresh()->status)->toBe('PARTIAL');
    expect((float) $invoice->fresh()->remaining_amount)->toBe(300000.0);
});

it('salesman dapat skip collection dengan alasan', function () {
    ['salesman' => $salesman, 'collectionTask' => $collectionTask] = makeCollectionFixture();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/collection/skip', [
        'collection_task_id' => $collectionTask->id,
        'reason' => 'no_money',
        'notes' => 'Outlet bilang belum ada uang.',
    ]);

    $response->assertStatus(200);
    expect($collectionTask->fresh()->status)->toBe('NO_PAYMENT');
});

it('skip dengan alasan reschedule mengubah status ke RESCHEDULED', function () {
    ['salesman' => $salesman, 'collectionTask' => $collectionTask] = makeCollectionFixture();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/collection/skip', [
        'collection_task_id' => $collectionTask->id,
        'reason' => 'reschedule',
    ]);

    $response->assertStatus(200);
    expect($collectionTask->fresh()->status)->toBe('RESCHEDULED');
});

it('salesman lain tidak bisa skip collection task milik orang lain', function () {
    ['collectionTask' => $collectionTask] = makeCollectionFixture();

    $other = User::factory()->create();
    $other->assignRole('SALESMAN');

    $response = $this->actingAs($other)->postJson('/pwa/api/collection/skip', [
        'collection_task_id' => $collectionTask->id,
        'reason' => 'no_money',
    ]);

    $response->assertStatus(403);
});
