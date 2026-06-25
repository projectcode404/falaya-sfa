<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'updated_by',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    public function getValue(): mixed
    {
        return match ($this->setting_type) {
            'NUMBER' => (float) $this->setting_value,
            'BOOLEAN' => filter_var($this->setting_value, FILTER_VALIDATE_BOOLEAN),
            'JSON' => json_decode($this->setting_value, true),
            default => $this->setting_value,
        };
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('setting_key', $key)->first();

        return $setting ? $setting->getValue() : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::where('setting_key', $key)->update([
            'setting_value' => is_array($value) ? json_encode($value) : (string) $value,
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);
    }
}
