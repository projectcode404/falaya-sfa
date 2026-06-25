<?php

use App\DomainServices\OperationalDateService;
use App\Models\OperationalDate;

beforeEach(function () {
    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);
});

it('can get current operational date', function () {
    $service = app(OperationalDateService::class);
    expect($service->current()->toDateString())->toBe(now()->toDateString());
});

it('is synced with calendar when date is today', function () {
    $service = app(OperationalDateService::class);
    expect($service->isSyncedWithCalendar())->toBeTrue();
});

it('is not synced when date is yesterday', function () {
    $yesterday = now()->subDay()->toDateString();

    OperationalDate::query()->update([
        'current_date_value' => $yesterday,
        'updated_at' => now(),
    ]);

    $service = app(OperationalDateService::class);
    expect($service->isSyncedWithCalendar())->toBeFalse();
});
