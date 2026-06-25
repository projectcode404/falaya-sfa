<?php

use App\Actions\Visit\CheckinVisitAction;
use App\Actions\Visit\CheckoutVisitAction;
use App\Actions\Visit\CreateUnplannedVisitAction;
use App\Actions\Visit\GenerateVisitPlanForDayAction;
use App\Models\Area;
use App\Models\Customer;
use App\Models\OperationalDate;
use App\Models\User;
use App\Models\VisitPlan;
use App\Models\VisitSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

function setupVisitTest(): array
{
    Artisan::call('db:seed', ['--class' => 'SettingsSeeder']);

    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);

    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Area Visit Test',
        'area_code' => 'AVT-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $salesman = User::factory()->create(['role' => 'SALESMAN']);
    $salesman->assignRole('SALESMAN');

    $customer = Customer::create([
        'customer_code' => 'CUST-'.uniqid(),
        'customer_name' => 'Warung Test Visit',
        'address' => 'Jl. Test No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'latitude' => -6.200000,
        'longitude' => 106.816666,
        'radius_tolerance_meter' => 100,
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    return compact('admin', 'area', 'salesman', 'customer');
}

it('can generate visit plan from schedule', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'admin' => $admin] = setupVisitTest();

    $schedule = VisitSchedule::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => 1,
        'is_active' => true,
        'effective_from' => now()->toDateString(),
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $operationalDate = Carbon::parse('2026-06-23');
    $visitPlan = app(GenerateVisitPlanForDayAction::class)->execute($schedule, $operationalDate);

    expect($visitPlan)->not->toBeNull()
        ->and($visitPlan->status)->toBe('PLANNED')
        ->and($visitPlan->is_planned)->toBeTrue()
        ->and($visitPlan->salesman_id)->toBe($salesman->id);
});

it('does not duplicate visit plan for same customer salesman date', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'admin' => $admin] = setupVisitTest();

    $schedule = VisitSchedule::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => 1,
        'is_active' => true,
        'effective_from' => now()->toDateString(),
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $date = Carbon::parse('2026-06-23');
    app(GenerateVisitPlanForDayAction::class)->execute($schedule, $date);
    $second = app(GenerateVisitPlanForDayAction::class)->execute($schedule, $date);

    expect($second)->toBeNull();
    expect(VisitPlan::count())->toBe(1);
});

it('can create unplanned visit', function () {
    ['salesman' => $salesman, 'customer' => $customer] = setupVisitTest();

    actingAsRole('SALESMAN');

    $visitPlan = app(CreateUnplannedVisitAction::class)->execute(
        $salesman->id,
        $customer->id,
        now()->toDateString()
    );

    expect($visitPlan->is_planned)->toBeFalse()
        ->and($visitPlan->visit_schedule_id)->toBeNull()
        ->and($visitPlan->status)->toBe('PLANNED');
});

it('can checkin visit within radius', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'admin' => $admin] = setupVisitTest();

    $schedule = VisitSchedule::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => now()->dayOfWeekIso,
        'is_active' => true,
        'effective_from' => now()->toDateString(),
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $visitPlan = app(GenerateVisitPlanForDayAction::class)->execute($schedule, now());

    actingAsRole('SALESMAN');
    $realization = app(CheckinVisitAction::class)->execute(
        $visitPlan,
        [
            'latitude' => -6.200001,
            'longitude' => 106.816666,
            'accuracy' => 10,
            'unavailable' => false,
        ],
        (string) Str::uuid()
    );

    expect($realization->checkin_at)->not->toBeNull()
        ->and($realization->gps_unavailable)->toBeFalse()
        ->and($visitPlan->fresh()->status)->toBe('IN_PROGRESS');
});

it('blocks checkin when outside radius', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'admin' => $admin] = setupVisitTest();

    $schedule = VisitSchedule::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => now()->dayOfWeekIso,
        'is_active' => true,
        'effective_from' => now()->toDateString(),
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $visitPlan = app(GenerateVisitPlanForDayAction::class)->execute($schedule, now());

    actingAsRole('SALESMAN');
    expect(fn () => app(CheckinVisitAction::class)->execute(
        $visitPlan,
        [
            'latitude' => -6.210000,
            'longitude' => 106.816666,
            'accuracy' => 10,
            'unavailable' => false,
        ],
        (string) Str::uuid()
    ))->toThrow(RuntimeException::class);
});

it('allows checkin when gps unavailable', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'admin' => $admin] = setupVisitTest();

    $schedule = VisitSchedule::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => now()->dayOfWeekIso,
        'is_active' => true,
        'effective_from' => now()->toDateString(),
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $visitPlan = app(GenerateVisitPlanForDayAction::class)->execute($schedule, now());

    actingAsRole('SALESMAN');
    $realization = app(CheckinVisitAction::class)->execute(
        $visitPlan,
        ['unavailable' => true],
        (string) Str::uuid()
    );

    expect($realization->gps_unavailable)->toBeTrue()
        ->and($realization->checkin_latitude)->toBeNull();
});

it('can checkout visit', function () {
    ['salesman' => $salesman, 'customer' => $customer, 'admin' => $admin] = setupVisitTest();

    $schedule = VisitSchedule::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => now()->dayOfWeekIso,
        'is_active' => true,
        'effective_from' => now()->toDateString(),
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $visitPlan = app(GenerateVisitPlanForDayAction::class)->execute($schedule, now());

    actingAsRole('SALESMAN');
    app(CheckinVisitAction::class)->execute(
        $visitPlan,
        ['unavailable' => true],
        (string) Str::uuid()
    );

    $realization = app(CheckoutVisitAction::class)->execute($visitPlan);

    expect($realization->checkout_at)->not->toBeNull()
        ->and($visitPlan->fresh()->status)->toBe('NO_ORDER');
});
