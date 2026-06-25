<?php

namespace App\Actions\Setting;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class UpdateSettingAction
{
    public function execute(string $key, mixed $value): Setting
    {
        return DB::transaction(function () use ($key, $value) {
            $setting = Setting::where('setting_key', $key)->firstOrFail();

            $setting->update([
                'setting_value' => is_array($value) ? json_encode($value) : (string) $value,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            return $setting->fresh();
        });
    }
}
