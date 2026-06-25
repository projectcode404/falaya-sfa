<?php

use App\Actions\Closing\ExecuteDailyClosingAction;
use App\Actions\Visit\GenerateVisitPlanForDayAction;
use App\Events\DailyClosingExecuted;
use App\Jobs\GenerateVisitPlanForNewDayJob;
use App\Listeners\DispatchVisitPlanGeneration;
use App\Models\Area;
use App\Models\Customer;
use App\Models\OperationalDate;
use App\Models\User;
use App\Models\VisitPlan;
use App\Models\VisitSchedule;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => SettingsSeeder::class]);
    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);
});

// ── Event dispatch after closing ──────────────────────────────────────────────

it('DailyClosingExecuted event is dispatched after successful closing', function () {
    Event::fake([DailyClosingExecuted::class]);

    $admin = actingAsRole('ADMIN');
    app(ExecuteDailyClosingAction::class)->execute();

    Event::assertDispatched(DailyClosingExecuted::class);
});

it('DailyClosingExecuted event carries correct dates', function () {
    Event::fake([DailyClosingExecuted::class]);

    $admin = actingAsRole('ADMIN');
    $today = now()->toDateString();

    app(ExecuteDailyClosingAction::class)->execute();

    Event::assertDispatched(DailyClosingExecuted::class, function ($event) use ($today) {
        return $event->previousOperationalDate->toDateString() === $today
            && $event->newOperationalDate->toDateString() === now()->addDay()->toDateString();
    });
});

// ── GenerateVisitPlanForNewDayJob ─────────────────────────────────────────────

it('GenerateVisitPlanForNewDayJob is dispatched to critical queue by listener', function () {
    Queue::fake();

    // Trigger listener langsung -- ShouldQueue listener tidak jalan saat Queue::fake()
    // karena Queue::fake() memblokir listener yang queued
    $listener = new DispatchVisitPlanGeneration;
    $event = new DailyClosingExecuted(now()->addDay(), now());
    $listener->handle($event);

    Queue::assertPushedOn('critical', GenerateVisitPlanForNewDayJob::class);
});

it('GenerateVisitPlanForNewDayJob generates visit plans from active schedules', function () {
    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Queue Test Area',
        'area_code' => 'QTA-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $salesman = User::factory()->create(['role' => 'SALESMAN']);
    $salesman->assignRole('SALESMAN');

    $customer = Customer::create([
        'customer_code' => 'QTC-'.uniqid(),
        'customer_name' => 'Queue Test Customer',
        'address' => 'Jl. Queue',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $tomorrow = now()->addDay();

    VisitSchedule::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => $tomorrow->dayOfWeekIso,
        'is_active' => true,
        'effective_from' => now()->toDateString(),
        'effective_to' => null,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $job = new GenerateVisitPlanForNewDayJob($tomorrow);
    $job->handle(app(GenerateVisitPlanForDayAction::class));

    $visitPlans = VisitPlan::where('operational_date', $tomorrow->toDateString())
        ->where('is_planned', true)
        ->get();

    expect($visitPlans)->toHaveCount(1);
    expect($visitPlans->first()->salesman_id)->toBe($salesman->id);
    expect($visitPlans->first()->customer_id)->toBe($customer->id);
});

it('GenerateVisitPlanForNewDayJob is idempotent — running twice does not duplicate plans', function () {
    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Idem Queue Area',
        'area_code' => 'IQA-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $salesman = User::factory()->create(['role' => 'SALESMAN']);
    $salesman->assignRole('SALESMAN');

    $customer = Customer::create([
        'customer_code' => 'IQC-'.uniqid(),
        'customer_name' => 'Idem Queue Customer',
        'address' => 'Jl. Idem',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $tomorrow = now()->addDay();

    VisitSchedule::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => $tomorrow->dayOfWeekIso,
        'is_active' => true,
        'effective_from' => now()->toDateString(),
        'effective_to' => null,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $action = app(GenerateVisitPlanForDayAction::class);
    $job = new GenerateVisitPlanForNewDayJob($tomorrow);

    $job->handle($action);
    $job->handle($action);

    $count = VisitPlan::where('operational_date', $tomorrow->toDateString())
        ->where('is_planned', true)
        ->count();

    expect($count)->toBe(1);
});

it('GenerateVisitPlanForNewDayJob failed method logs critical error', function () {
    Log::shouldReceive('critical')
        ->once()
        ->with('Gagal generate Visit Plan untuk hari baru', Mockery::type('array'));

    $job = new GenerateVisitPlanForNewDayJob(now()->addDay());
    $job->failed(new RuntimeException('Simulasi kegagalan job'));
});

it('inactive visit schedules are not included in generated visit plans', function () {
    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Inactive Schedule Area',
        'area_code' => 'ISA-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $salesman = User::factory()->create(['role' => 'SALESMAN']);
    $salesman->assignRole('SALESMAN');

    $customer = Customer::create([
        'customer_code' => 'ISC-'.uniqid(),
        'customer_name' => 'Inactive Schedule Customer',
        'address' => 'Jl. Inactive',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $tomorrow = now()->addDay();

    VisitSchedule::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer->id,
        'day_of_week' => $tomorrow->dayOfWeekIso,
        'is_active' => false,
        'effective_from' => now()->toDateString(),
        'effective_to' => null,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $job = new GenerateVisitPlanForNewDayJob($tomorrow);
    $job->handle(app(GenerateVisitPlanForDayAction::class));

    $count = VisitPlan::where('operational_date', $tomorrow->toDateString())
        ->where('is_planned', true)
        ->count();

    expect($count)->toBe(0);
});
