<?php

use App\Models\Area;
use App\Models\Customer;
use App\Models\OperationalDate;
use App\Models\Product;
use App\Models\StockBalance;
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

function makeSalesOrderFixture(string $customerType = 'CASH'): array
{
    $salesman = User::factory()->create();
    $salesman->assignRole('SALESMAN');

    $area = Area::create([
        'area_name' => 'Test Area SO',
        'area_code' => 'TASO-'.uniqid(),
        'is_active' => true,
        'created_by' => $salesman->id,
    ]);

    $customer = Customer::create([
        'customer_code' => 'CST-'.uniqid(),
        'customer_name' => 'Outlet Test',
        'address' => 'Jl. Outlet No. 1',
        'area_id' => $area->id,
        'customer_type' => $customerType,
        'status' => 'ACTIVE',
        'credit_limit' => $customerType === 'CREDIT' ? 1000000 : null,
        'credit_term_days' => $customerType === 'CREDIT' ? 14 : null,
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $product = Product::create([
        'product_code' => 'PRD-'.uniqid(),
        'product_name' => 'Keripik Original',
        'unit' => 'pcs',
        'selling_price' => 5000,
        'is_active' => true,
        'created_by' => $salesman->id,
        'created_at' => now(),
    ]);

    // Beri stok ke salesman
    StockBalance::create([
        'product_id' => $product->id,
        'holder_type' => 'SALESMAN',
        'holder_id' => $salesman->id,
        'condition' => 'GOOD',
        'qty' => 100,
        'updated_at' => now(),
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

    // Visit realization (sudah checkin)
    VisitRealization::create([
        'visit_plan_id' => $visitPlan->id,
        'checkin_at' => now(),
        'gps_unavailable' => true,
        'gps_low_accuracy' => false,
        'created_at' => now(),
    ]);

    return compact('salesman', 'customer', 'product', 'visitPlan');
}

// --- STORE ---

it('salesman dapat membuat sales order CASH', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'product' => $product, 'visitPlan' => $visitPlan] = makeSalesOrderFixture();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/sales-orders', [
        'visit_plan_id' => $visitPlan->id,
        'customer_id' => $customer->id,
        'payment_type' => 'CASH',
        'items' => [
            ['product_id' => $product->id, 'qty' => 5, 'unit_price' => 5000],
        ],
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['status' => 'DRAFT']);
});

it('validasi gagal bila items kosong', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'visitPlan' => $visitPlan] = makeSalesOrderFixture();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/sales-orders', [
        'visit_plan_id' => $visitPlan->id,
        'customer_id' => $customer->id,
        'payment_type' => 'CASH',
        'items' => [],
    ]);

    $response->assertStatus(422);
});

// --- POST ---

it('salesman dapat post sales order CASH dan stok berkurang', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'product' => $product, 'visitPlan' => $visitPlan] = makeSalesOrderFixture();

    // Buat DRAFT dulu
    $storeResp = $this->actingAs($salesman)->postJson('/pwa/api/sales-orders', [
        'visit_plan_id' => $visitPlan->id,
        'customer_id' => $customer->id,
        'payment_type' => 'CASH',
        'items' => [
            ['product_id' => $product->id, 'qty' => 10, 'unit_price' => 5000],
        ],
    ]);
    $salesOrderId = $storeResp->json('sales_order_id');

    $postResp = $this->actingAs($salesman)->postJson("/pwa/api/sales-orders/{$salesOrderId}/post");

    $postResp->assertStatus(200)
        ->assertJsonFragment(['status' => 'POSTED']);

    $balance = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->first();

    expect((float) $balance->qty)->toBe(90.0);
});

it('post gagal bila stok tidak cukup', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'product' => $product, 'visitPlan' => $visitPlan] = makeSalesOrderFixture();

    $storeResp = $this->actingAs($salesman)->postJson('/pwa/api/sales-orders', [
        'visit_plan_id' => $visitPlan->id,
        'customer_id' => $customer->id,
        'payment_type' => 'CASH',
        'items' => [
            ['product_id' => $product->id, 'qty' => 999, 'unit_price' => 5000],
        ],
    ]);
    $salesOrderId = $storeResp->json('sales_order_id');

    $postResp = $this->actingAs($salesman)->postJson("/pwa/api/sales-orders/{$salesOrderId}/post");

    $postResp->assertStatus(422);

    // Stok tidak boleh berubah
    $balance = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->first();
    expect((float) $balance->qty)->toBe(100.0);
});

it('salesman lain tidak bisa post sales order milik orang lain', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'product' => $product, 'visitPlan' => $visitPlan] = makeSalesOrderFixture();

    $storeResp = $this->actingAs($salesman)->postJson('/pwa/api/sales-orders', [
        'visit_plan_id' => $visitPlan->id,
        'customer_id' => $customer->id,
        'payment_type' => 'CASH',
        'items' => [
            ['product_id' => $product->id, 'qty' => 5, 'unit_price' => 5000],
        ],
    ]);
    $salesOrderId = $storeResp->json('sales_order_id');

    $other = User::factory()->create();
    $other->assignRole('SALESMAN');

    $postResp = $this->actingAs($other)->postJson("/pwa/api/sales-orders/{$salesOrderId}/post");
    $postResp->assertStatus(403);
});

// --- CANCEL ---

it('salesman dapat cancel sales order DRAFT', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'product' => $product, 'visitPlan' => $visitPlan] = makeSalesOrderFixture();

    $storeResp = $this->actingAs($salesman)->postJson('/pwa/api/sales-orders', [
        'visit_plan_id' => $visitPlan->id,
        'customer_id' => $customer->id,
        'payment_type' => 'CASH',
        'items' => [
            ['product_id' => $product->id, 'qty' => 5, 'unit_price' => 5000],
        ],
    ]);
    $salesOrderId = $storeResp->json('sales_order_id');

    $cancelResp = $this->actingAs($salesman)->postJson("/pwa/api/sales-orders/{$salesOrderId}/cancel", [
        'reason' => 'Outlet tutup mendadak.',
    ]);

    $cancelResp->assertStatus(200)
        ->assertJsonFragment(['status' => 'CANCELLED']);
});

it('cancel posted SO mengembalikan stok ke salesman', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'product' => $product, 'visitPlan' => $visitPlan] = makeSalesOrderFixture();

    $storeResp = $this->actingAs($salesman)->postJson('/pwa/api/sales-orders', [
        'visit_plan_id' => $visitPlan->id,
        'customer_id' => $customer->id,
        'payment_type' => 'CASH',
        'items' => [
            ['product_id' => $product->id, 'qty' => 10, 'unit_price' => 5000],
        ],
    ]);
    $salesOrderId = $storeResp->json('sales_order_id');

    $this->actingAs($salesman)->postJson("/pwa/api/sales-orders/{$salesOrderId}/post");

    $balanceAfterPost = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->first();
    expect((float) $balanceAfterPost->qty)->toBe(90.0);

    $this->actingAs($salesman)->postJson("/pwa/api/sales-orders/{$salesOrderId}/cancel", [
        'reason' => 'Test reversal.',
    ]);

    $balanceAfterCancel = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->first();
    expect((float) $balanceAfterCancel->qty)->toBe(100.0);
});
