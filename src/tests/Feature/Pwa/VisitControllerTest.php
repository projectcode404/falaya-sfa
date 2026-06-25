<?php

use App\Models\Area;
use App\Models\Customer;
use App\Models\OperationalDate;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Support\Str;

beforeEach(function () {
    if (OperationalDate::count() === 0) {
        OperationalDate::create([
            'current_date_value' => now()->toDateString(),
            'is_closing_in_progress' => false,
            'updated_at' => now(),
        ]);
    }
});

function makeVisitPlan(): array
{
    $salesman = User::factory()->create();
    $salesman->assignRole('SALESMAN');

    $area = Area::create([
        'area_name' => 'Test Area',
        'area_code' => 'TA-'.uniqid(),
        'is_active' => true,
        'created_by' => $salesman->id,
    ]);

    $customer = Customer::create([
        'customer_code' => 'CUST-'.uniqid(),
        'customer_name' => 'Toko Test',
        'address' => 'Jl. Test No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'latitude' => -6.200000,
        'longitude' => 106.816666,
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $visitPlan = VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => true,
        'area_id_snapshot' => $area->id,
        'status' => 'PLANNED',
        'created_at' => now(),
    ]);

    return compact('salesman', 'area', 'customer', 'visitPlan');
}

// --- CHECKIN ---

it('salesman dapat checkin dengan GPS valid dalam radius', function () {
    ['salesman' => $salesman, 'visitPlan' => $visitPlan] = makeVisitPlan();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/visits/checkin', [
        'visit_plan_id' => $visitPlan->id,
        'idempotency_key' => (string) Str::uuid(),
        'latitude' => -6.200001,
        'longitude' => 106.816667,
        'accuracy' => 10,
        'gps_unavailable' => false,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'Check-in berhasil.']);

    expect($visitPlan->fresh()->status)->toBe('IN_PROGRESS');
});

it('checkin gagal bila GPS di luar radius', function () {
    ['salesman' => $salesman, 'visitPlan' => $visitPlan, 'customer' => $customer] = makeVisitPlan();

    $customer->update(['radius_tolerance_meter' => 100]);

    $response = $this->actingAs($salesman)->postJson('/pwa/api/visits/checkin', [
        'visit_plan_id' => $visitPlan->id,
        'idempotency_key' => (string) Str::uuid(),
        'latitude' => -6.210000,
        'longitude' => 106.820000,
        'accuracy' => 10,
        'gps_unavailable' => false,
    ]);

    $response->assertStatus(422);
    expect($visitPlan->fresh()->status)->toBe('PLANNED');
});

it('checkin diizinkan bila GPS tidak tersedia', function () {
    ['salesman' => $salesman, 'visitPlan' => $visitPlan] = makeVisitPlan();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/visits/checkin', [
        'visit_plan_id' => $visitPlan->id,
        'idempotency_key' => (string) Str::uuid(),
        'gps_unavailable' => true,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['gps_unavailable' => true]);
});

it('checkin idempotent — submit ulang key yang sama return data yang sama', function () {
    ['salesman' => $salesman, 'visitPlan' => $visitPlan] = makeVisitPlan();
    $key = (string) Str::uuid();

    $payload = [
        'visit_plan_id' => $visitPlan->id,
        'idempotency_key' => $key,
        'gps_unavailable' => true,
    ];

    $first = $this->actingAs($salesman)->postJson('/pwa/api/visits/checkin', $payload);
    $second = $this->actingAs($salesman)->postJson('/pwa/api/visits/checkin', $payload);

    $first->assertStatus(201);
    $second->assertStatus(200);
    expect($first->json('realization_id'))->toBe($second->json('realization_id'));
});

it('salesman lain tidak bisa checkin visit milik salesman lain', function () {
    ['visitPlan' => $visitPlan] = makeVisitPlan();
    $other = User::factory()->create();
    $other->assignRole('SALESMAN');

    $response = $this->actingAs($other)->postJson('/pwa/api/visits/checkin', [
        'visit_plan_id' => $visitPlan->id,
        'idempotency_key' => (string) Str::uuid(),
        'gps_unavailable' => true,
    ]);

    $response->assertStatus(403);
});

// --- CHECKOUT ---

it('salesman dapat checkout setelah checkin', function () {
    ['salesman' => $salesman, 'visitPlan' => $visitPlan] = makeVisitPlan();

    $this->actingAs($salesman)->postJson('/pwa/api/visits/checkin', [
        'visit_plan_id' => $visitPlan->id,
        'idempotency_key' => (string) Str::uuid(),
        'gps_unavailable' => true,
    ]);

    $response = $this->actingAs($salesman)->postJson('/pwa/api/visits/checkout', [
        'visit_plan_id' => $visitPlan->id,
        'latitude' => -6.200001,
        'longitude' => 106.816667,
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Check-out berhasil.']);

    expect($visitPlan->fresh()->status)->toBe('NO_ORDER');
});

it('checkout gagal bila visit belum checkin', function () {
    ['salesman' => $salesman, 'visitPlan' => $visitPlan] = makeVisitPlan();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/visits/checkout', [
        'visit_plan_id' => $visitPlan->id,
    ]);

    $response->assertStatus(409);
});

// --- UNPLANNED VISIT ---

it('salesman dapat membuat unplanned visit ke customer berbeda', function () {
    // Pakai salesman + area dari makeVisitPlan (sudah punya 1 visit plan),
    // lalu buat customer BARU di area yang sama — tidak ada konflik unique constraint
    ['salesman' => $salesman, 'area' => $area] = makeVisitPlan();

    $customerBaru = Customer::create([
        'customer_code' => 'UNPL-'.uniqid(),
        'customer_name' => 'Toko Baru Unplanned',
        'address' => 'Jl. Unplanned No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($salesman)->postJson('/pwa/api/visits/unplanned', [
        'customer_id' => $customerBaru->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'Kunjungan tidak terjadwal berhasil dibuat.']);
});

it('endpoint checkin memerlukan autentikasi', function () {
    $response = $this->postJson('/pwa/api/visits/checkin', []);
    $response->assertStatus(401);
});
