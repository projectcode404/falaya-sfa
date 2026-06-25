<?php

use App\Actions\Closing\ExecuteDailyClosingAction;
use App\Actions\Closing\ValidateClosingChecklistAction;
use App\DomainServices\OperationalDateService;
use App\Models\Area;
use App\Models\Customer;
use App\Models\DailyClosing;
use App\Models\OperationalDate;
use App\Models\User;
use App\Models\VisitPlan;

function setupClosingTest(): array
{
    Artisan::call('db:seed', ['--class' => 'SettingsSeeder']);

    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);

    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Area Closing Test',
        'area_code' => 'CLT-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $salesman = User::factory()->create(['role' => 'SALESMAN']);
    $salesman->assignRole('SALESMAN');

    $customer = Customer::create([
        'customer_code' => 'CUST-CLT-'.uniqid(),
        'customer_name' => 'Toko Closing Test',
        'address' => 'Jl. Test No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    return compact('admin', 'salesman', 'customer', 'area');
}

it('checklist passes when no blocking items', function () {
    setupClosingTest();

    $result = app(ValidateClosingChecklistAction::class)->execute();

    expect($result['can_close'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

it('checklist fails when visit is in progress', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'area' => $area, 'admin' => $admin] = setupClosingTest();

    VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => true,
        'area_id_snapshot' => $area->id,
        'status' => 'IN_PROGRESS',
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $result = app(ValidateClosingChecklistAction::class)->execute();

    expect($result['can_close'])->toBeFalse()
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0]['type'])->toBe('visit_in_progress');
});

it('can execute daily closing and advance operational date', function () {
    setupClosingTest();

    $today = now()->toDateString();
    $operationalDateBefore = app(OperationalDateService::class)->current()->toDateString();

    $dailyClosing = app(ExecuteDailyClosingAction::class)->execute();

    $operationalDateAfter = OperationalDate::first()->current_date_value;

    expect($dailyClosing)->toBeInstanceOf(DailyClosing::class)
        ->and($dailyClosing->operational_date->toDateString())->toBe($today)
        ->and($operationalDateAfter)->not->toBe($operationalDateBefore);
});

it('closing marks planned visit plans as skipped', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'area' => $area, 'admin' => $admin] = setupClosingTest();

    $visitPlan = VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => true,
        'area_id_snapshot' => $area->id,
        'status' => 'PLANNED',
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    app(ExecuteDailyClosingAction::class)->execute();

    expect($visitPlan->fresh()->status)->toBe('SKIPPED');
});

it('cannot execute closing when checklist fails', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'area' => $area, 'admin' => $admin] = setupClosingTest();

    VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => true,
        'area_id_snapshot' => $area->id,
        'status' => 'IN_PROGRESS',
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    expect(fn () => app(ExecuteDailyClosingAction::class)->execute())
        ->toThrow(LogicException::class);
});
