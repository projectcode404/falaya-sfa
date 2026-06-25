<?php

use App\Actions\Customer\CreateCustomerAction;
use App\Actions\VisitSchedule\CreateVisitScheduleAction;
use App\Actions\VisitSchedule\DeactivateVisitScheduleAction;
use App\Models\Area;
use App\Models\User;
use App\Models\VisitSchedule;
use Illuminate\Database\QueryException;

function createTestData(): array
{
    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Area Test',
        'area_code' => 'AT-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $salesman = User::factory()->create(['role' => 'SALESMAN']);
    $salesman->assignRole('SALESMAN');

    $customer = app(CreateCustomerAction::class)->execute([
        'customer_code' => 'CUST-'.uniqid(),
        'customer_name' => 'Warung Test',
        'address' => 'Jl. Test No. 1',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
    ]);

    return compact('admin', 'area', 'salesman', 'customer');
}

it('can create visit schedule', function () {
    ['salesman' => $salesman, 'customer' => $customer] = createTestData();

    $schedule = app(CreateVisitScheduleAction::class)->execute([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => 1, // Senin
        'effective_from' => now()->toDateString(),
    ]);

    expect($schedule)->toBeInstanceOf(VisitSchedule::class)
        ->and($schedule->day_of_week)->toBe(1)
        ->and($schedule->is_active)->toBeTrue()
        ->and($schedule->effective_to)->toBeNull();
});

it('can deactivate visit schedule', function () {
    ['salesman' => $salesman, 'customer' => $customer] = createTestData();

    $schedule = app(CreateVisitScheduleAction::class)->execute([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => 2,
        'effective_from' => now()->toDateString(),
    ]);

    app(DeactivateVisitScheduleAction::class)->execute($schedule);

    expect($schedule->fresh()->is_active)->toBeFalse()
        ->and($schedule->fresh()->effective_to)->not->toBeNull();
});

it('scope activeForDay returns only active schedules for given day', function () {
    ['salesman' => $salesman, 'customer' => $customer] = createTestData();

    app(CreateVisitScheduleAction::class)->execute([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => 3, // Rabu
        'effective_from' => now()->toDateString(),
    ]);

    app(CreateVisitScheduleAction::class)->execute([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => 4, // Kamis
        'effective_from' => now()->toDateString(),
    ]);

    $rabuSchedules = VisitSchedule::activeForDay(3)->get();
    expect($rabuSchedules)->toHaveCount(1)
        ->and($rabuSchedules->first()->day_of_week)->toBe(3);
});

it('day_of_week constraint rejects invalid values', function () {
    expect(fn () => DB::table('visit_schedules')->insert([
        'salesman_id' => 1,
        'customer_id' => 1,
        'day_of_week' => 8, // Invalid -- max 7
        'is_active' => true,
        'effective_from' => now()->toDateString(),
        'created_by' => 1,
        'created_at' => now(),
    ]))->toThrow(QueryException::class);
});
