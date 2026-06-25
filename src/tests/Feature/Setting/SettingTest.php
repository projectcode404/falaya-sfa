<?php

use App\Actions\Setting\UpdateSettingAction;
use App\Models\Setting;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed(SettingsSeeder::class);
});

it('can get setting value', function () {
    $value = Setting::get('default_radius_tolerance_meter');
    expect($value)->toBe(100.0);
});

it('can get setting with correct type casting', function () {
    expect(Setting::get('default_radius_tolerance_meter'))->toBeFloat()
        ->and(Setting::get('company_name'))->toBeString()
        ->and(Setting::get('company_name'))->toBe('Falaya');
});

it('can update setting', function () {
    actingAsRole('OWNER');
    $action = app(UpdateSettingAction::class);

    $setting = $action->execute('default_radius_tolerance_meter', 150);

    expect(Setting::get('default_radius_tolerance_meter'))->toBe(150.0);
});

it('returns default when setting key not found', function () {
    $value = Setting::get('non_existent_key', 'default_value');
    expect($value)->toBe('default_value');
});
