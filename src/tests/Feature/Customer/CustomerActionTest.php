<?php

use App\Actions\Customer\ApproveCustomerCreditAction;
use App\Actions\Customer\CreateCustomerAction;
use App\Actions\Customer\DeactivateCustomerAction;
use App\Actions\Customer\RejectCustomerCreditAction;
use App\Models\Area;

function createTestArea(): Area
{
    $admin = actingAsRole('ADMIN');

    return Area::create([
        'area_name' => 'Area Test',
        'area_code' => 'AT-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);
}

it('can create cash customer and immediately active', function () {
    $area = createTestArea();
    $salesman = actingAsRole('SALESMAN');

    $customer = app(CreateCustomerAction::class)->execute([
        'customer_code' => 'CUST-001',
        'customer_name' => 'Warung Bu Tini',
        'address' => 'Jl. Merdeka No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
    ]);

    expect($customer->status)->toBe('ACTIVE')
        ->and($customer->customer_type)->toBe('CASH')
        ->and($customer->requested_by)->toBe($salesman->id)
        ->and($customer->isActive())->toBeTrue();
});

it('can create credit customer with pending approval status', function () {
    $area = createTestArea();
    actingAsRole('SALESMAN');

    $customer = app(CreateCustomerAction::class)->execute([
        'customer_code' => 'CUST-002',
        'customer_name' => 'Toko Sumber Rejeki',
        'address' => 'Jl. Merdeka No. 2',
        'area_id' => $area->id,
        'customer_type' => 'CREDIT',
        'credit_limit' => 2000000,
        'credit_term_days' => 14,
        'owner_name' => 'Pak Budi',
        'owner_phone' => '08123456789',
    ]);

    expect($customer->status)->toBe('PENDING_APPROVAL')
        ->and($customer->isPendingApproval())->toBeTrue()
        ->and($customer->credit_limit)->toBe('2000000.00');
});

it('owner can approve credit customer', function () {
    $area = createTestArea();
    actingAsRole('SALESMAN');

    $customer = app(CreateCustomerAction::class)->execute([
        'customer_code' => 'CUST-003',
        'customer_name' => 'CV Makmur Jaya',
        'address' => 'Jl. Merdeka No. 3',
        'area_id' => $area->id,
        'customer_type' => 'CREDIT',
        'credit_limit' => 2000000,
        'credit_term_days' => 14,
    ]);

    $owner = actingAsRole('OWNER');
    $approved = app(ApproveCustomerCreditAction::class)->execute($customer, 1500000);

    expect($approved->status)->toBe('ACTIVE')
        ->and($approved->approved_by)->toBe($owner->id)
        ->and($approved->credit_limit)->toBe('1500000.00')
        ->and($approved->approved_at)->not->toBeNull();
});

it('owner can reject credit customer', function () {
    $area = createTestArea();
    actingAsRole('SALESMAN');

    $customer = app(CreateCustomerAction::class)->execute([
        'customer_code' => 'CUST-004',
        'customer_name' => 'Toko Berisiko',
        'address' => 'Jl. Merdeka No. 4',
        'area_id' => $area->id,
        'customer_type' => 'CREDIT',
        'credit_limit' => 5000000,
        'credit_term_days' => 30,
    ]);

    $owner = actingAsRole('OWNER');
    $rejected = app(RejectCustomerCreditAction::class)->execute($customer, 'Riwayat pembayaran buruk');

    expect($rejected->status)->toBe('REJECTED')
        ->and($rejected->rejected_by)->toBe($owner->id)
        ->and($rejected->approval_notes)->toBe('Riwayat pembayaran buruk');
});

it('cannot approve customer that is not pending', function () {
    $area = createTestArea();
    actingAsRole('SALESMAN');

    $customer = app(CreateCustomerAction::class)->execute([
        'customer_code' => 'CUST-005',
        'customer_name' => 'Warung Cash',
        'address' => 'Jl. Merdeka No. 5',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
    ]);

    actingAsRole('OWNER');
    expect(fn () => app(ApproveCustomerCreditAction::class)->execute($customer))
        ->toThrow(LogicException::class);
});

it('can deactivate customer', function () {
    $area = createTestArea();
    actingAsRole('SALESMAN');

    $customer = app(CreateCustomerAction::class)->execute([
        'customer_code' => 'CUST-006',
        'customer_name' => 'Warung Tutup',
        'address' => 'Jl. Merdeka No. 6',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
    ]);

    app(DeactivateCustomerAction::class)->execute($customer);

    expect($customer->fresh()->status)->toBe('INACTIVE');
});
