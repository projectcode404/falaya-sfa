<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property Customer $customer
 */
class VisitSchedule extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'salesman_id',
        'customer_id',
        'day_of_week',
        'is_active',
        'effective_from',
        'effective_to',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActiveForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('effective_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now()->toDateString());
            });
    }
}
