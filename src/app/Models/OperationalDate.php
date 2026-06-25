<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationalDate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'current_date_value',
        'is_closing_in_progress',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'current_date_value' => 'date',
            'is_closing_in_progress' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }
}
