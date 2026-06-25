<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClosing extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'operational_date',
        'closed_by',
        'closed_at',
        'total_salesman_active',
        'total_visit_skipped',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
