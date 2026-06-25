<?php

use App\Models\Area;
use App\Models\User;

function makeAdminAndArea(): array
{
    $salesman = User::factory()->create();
    $salesman->assignRole('SALESMAN');

    $area = Area::create([
        'area_name' => 'Area Customer Test',
        'area_code' => 'ACT-'.uniqid(),
        'is_active' => true,
        'created_by' => $salesman->id,
    ]);

    return compact('salesman', 'area');
}

it('salesman dapat membuat customer CASH langsung ACTIVE', function () {
    ['salesman' => $salesman, 'area' => $area] = makeAdminAndArea();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/customers', [
        'customer_code' => 'NEW-'.uniqid(),
        'customer_name' => 'Warung Baru',
        'address' => 'Jl. Baru No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['status' => 'ACTIVE']);
});

it('salesman dapat membuat customer CREDIT dengan status PENDING_APPROVAL', function () {
    ['salesman' => $salesman, 'area' => $area] = makeAdminAndArea();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/customers', [
        'customer_code' => 'CRD-'.uniqid(),
        'customer_name' => 'Toko Kredit',
        'address' => 'Jl. Kredit No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CREDIT',
        'credit_limit' => 2000000,
        'credit_term_days' => 14,
        'owner_name' => 'Pak Budi',
        'owner_phone' => '08123456789',
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['status' => 'PENDING_APPROVAL']);
});

it('gagal bila customer_code sudah dipakai', function () {
    ['salesman' => $salesman, 'area' => $area] = makeAdminAndArea();
    $code = 'DUP-'.uniqid();

    $this->actingAs($salesman)->postJson('/pwa/api/customers', [
        'customer_code' => $code,
        'customer_name' => 'Toko A',
        'address' => 'Jl. A',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
    ]);

    $response = $this->actingAs($salesman)->postJson('/pwa/api/customers', [
        'customer_code' => $code,
        'customer_name' => 'Toko B',
        'address' => 'Jl. B',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
    ]);

    $response->assertStatus(422);
});

it('customer CREDIT wajib isi credit_limit dan credit_term_days', function () {
    ['salesman' => $salesman, 'area' => $area] = makeAdminAndArea();

    $response = $this->actingAs($salesman)->postJson('/pwa/api/customers', [
        'customer_code' => 'NOCRD-'.uniqid(),
        'customer_name' => 'Toko Tanpa Limit',
        'address' => 'Jl. X',
        'area_id' => $area->id,
        'customer_type' => 'CREDIT',
        // sengaja tidak isi credit_limit & credit_term_days
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['credit_limit', 'credit_term_days']);
});
