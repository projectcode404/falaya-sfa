<?php

use App\Actions\Inventory\CreateStockLoadingAction;
use App\Actions\Inventory\PostStockLoadingAction;
use App\Actions\Sales\CreateSalesOrderAction;
use App\Actions\Sales\PostSalesOrderAction;
use App\Actions\Visit\CheckinVisitAction;
use App\Models\Area;
use App\Models\Customer;
use App\Models\OperationalDate;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\VisitPlan;
use App\Models\VisitRealization;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => SettingsSeeder::class]);
    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);
});

// ── Helper ────────────────────────────────────────────────────────────────────

function setupIdempotencyBase(): array
{
    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Area Idempotency',
        'area_code' => 'AI-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $product = Product::create([
        'product_code' => 'KS-IDEM-'.uniqid(),
        'product_name' => 'Keripik Idempotency',
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
        'customer_code' => 'IDEM-'.uniqid(),
        'customer_name' => 'Toko Idempotency',
        'address' => 'Jl. Idem No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $visitPlan = VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => false,
        'area_id_snapshot' => $area->id,
        'status' => 'PLANNED',
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    return compact('admin', 'salesman', 'product', 'customer', 'visitPlan', 'area');
}

// ── Check-in Idempotency ──────────────────────────────────────────────────────

it('checkin with same idempotency key twice returns same realization', function () {
    ['salesman' => $salesman, 'visitPlan' => $visitPlan] = setupIdempotencyBase();
    actingAsRole('SALESMAN');

    $key = (string) Str::uuid();
    $gpsData = ['unavailable' => true];

    $result1 = app(CheckinVisitAction::class)->execute($visitPlan, $gpsData, $key);
    $result2 = app(CheckinVisitAction::class)->execute($visitPlan, $gpsData, $key);

    // Harus return record yang sama
    expect($result1->id)->toBe($result2->id);

    // Hanya ada 1 realization di database
    expect(VisitRealization::where('visit_plan_id', $visitPlan->id)->count())->toBe(1);
});

it('checkin with same idempotency key does not duplicate visit plan status update', function () {
    ['salesman' => $salesman, 'visitPlan' => $visitPlan] = setupIdempotencyBase();
    actingAsRole('SALESMAN');

    $key = (string) Str::uuid();
    $gpsData = ['unavailable' => true];

    app(CheckinVisitAction::class)->execute($visitPlan, $gpsData, $key);
    app(CheckinVisitAction::class)->execute($visitPlan, $gpsData, $key);
    app(CheckinVisitAction::class)->execute($visitPlan, $gpsData, $key); // 3x submit

    expect($visitPlan->fresh()->status)->toBe('IN_PROGRESS');
    expect(VisitRealization::count())->toBe(1);
});

// ── Sales Order Idempotency ───────────────────────────────────────────────────

it('posting sales order with same idempotency key twice does not double-deduct stock', function () {
    ['salesman' => $salesman, 'product' => $product, 'customer' => $customer, 'visitPlan' => $visitPlan]
        = setupIdempotencyBase();

    $visitPlan->update(['status' => 'IN_PROGRESS']);
    actingAsRole('SALESMAN');

    $idempotencyKey = (string) Str::uuid();

    $so = app(CreateSalesOrderAction::class)->execute(
        $visitPlan->id, $customer->id, $salesman->id, 'CASH',
        [['product_id' => $product->id, 'qty' => 10, 'unit_price' => 5000]]
    );

    // Set idempotency key di SO sebelum post
    $so->update(['idempotency_key' => $idempotencyKey]);

    // Post pertama kali -- berhasil
    $result1 = app(PostSalesOrderAction::class)->execute($so->fresh());
    expect($result1->status)->toBe('POSTED');

    $stockAfterFirst = (float) StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->where('condition', 'GOOD')
        ->value('qty');

    expect($stockAfterFirst)->toBe(40.0); // 50 - 10 = 40

    // Post kedua kali dengan SO yang sama (simulasi PWA retry)
    // Harus return SO yang sudah POSTED tanpa efek samping
    $result2 = app(PostSalesOrderAction::class)->execute($so->fresh());
    expect($result2->status)->toBe('POSTED');
    expect($result2->id)->toBe($result1->id);

    // Stok TIDAK boleh berkurang lagi
    $stockAfterSecond = (float) StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->where('condition', 'GOOD')
        ->value('qty');

    expect($stockAfterSecond)->toBe(40.0); // tetap 40, bukan 30
});

it('posting sales order without idempotency key still works normally', function () {
    ['salesman' => $salesman, 'product' => $product, 'customer' => $customer, 'visitPlan' => $visitPlan]
        = setupIdempotencyBase();

    $visitPlan->update(['status' => 'IN_PROGRESS']);
    actingAsRole('SALESMAN');

    $so = app(CreateSalesOrderAction::class)->execute(
        $visitPlan->id, $customer->id, $salesman->id, 'CASH',
        [['product_id' => $product->id, 'qty' => 5, 'unit_price' => 5000]]
    );

    // Tidak set idempotency_key
    expect($so->idempotency_key)->toBeNull();

    $result = app(PostSalesOrderAction::class)->execute($so);
    expect($result->status)->toBe('POSTED');
});

// ── Payment Idempotency (via PWA endpoint) ────────────────────────────────────

it('pwa checkin endpoint rejects duplicate idempotency key with same result', function () {
    ['salesman' => $salesman, 'visitPlan' => $visitPlan] = setupIdempotencyBase();

    $this->actingAs($salesman);

    $key = (string) Str::uuid();

    $response1 = $this->postJson('/pwa/api/visits/checkin', [
        'visit_plan_id' => $visitPlan->id,
        'idempotency_key' => $key,
        'gps_unavailable' => true,
    ]);

    $response2 = $this->postJson('/pwa/api/visits/checkin', [
        'visit_plan_id' => $visitPlan->id,
        'idempotency_key' => $key,
        'gps_unavailable' => true,
    ]);

    $response1->assertStatus(201);
    $response2->assertStatus(200); // idempotent -- return existing

    // ID realization harus sama
    expect($response1->json('realization_id'))->toBe($response2->json('realization_id'));

    // Hanya 1 realization
    expect(VisitRealization::where('visit_plan_id', $visitPlan->id)->count())->toBe(1);
});

it('pwa checkin with different idempotency keys on different visit plans creates separate realizations', function () {
    ['salesman' => $salesman, 'visitPlan' => $visitPlan, 'area' => $area, 'customer' => $customer]
        = setupIdempotencyBase();

    $visitPlan2 = VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'operational_date' => now()->addDay()->toDateString(),
        'is_planned' => false,
        'area_id_snapshot' => $area->id,
        'status' => 'PLANNED',
        'created_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $this->actingAs($salesman);

    $this->postJson('/pwa/api/visits/checkin', [
        'visit_plan_id' => $visitPlan->id,
        'idempotency_key' => (string) Str::uuid(),
        'gps_unavailable' => true,
    ])->assertStatus(201);

    $this->postJson('/pwa/api/visits/checkin', [
        'visit_plan_id' => $visitPlan2->id,
        'idempotency_key' => (string) Str::uuid(),
        'gps_unavailable' => true,
    ])->assertStatus(201);

    expect(VisitRealization::count())->toBe(2);
});
