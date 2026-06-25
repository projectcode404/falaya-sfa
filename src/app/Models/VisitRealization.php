<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitRealization extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'visit_plan_id',
        'checkin_latitude',
        'checkin_longitude',
        'checkin_accuracy_meter',
        'checkin_at',
        'gps_unavailable',
        'gps_low_accuracy',
        'photo_media_id',
        'checkout_latitude',
        'checkout_longitude',
        'checkout_at',
        'idempotency_key',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'checkin_at' => 'datetime',
            'checkout_at' => 'datetime',
            'gps_unavailable' => 'boolean',
            'gps_low_accuracy' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function visitPlan()
    {
        return $this->belongsTo(VisitPlan::class);
    }
}
