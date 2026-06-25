<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'payment_number',
        'customer_id',
        'collected_by',
        'visit_plan_id',
        'operational_date',
        'payment_method',
        'total_amount',
        'notes',
        'status',
        'idempotency_key',
        'created_by',
        'created_at',
        'posted_by',
        'posted_at',
        'void_by',
        'void_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'posted_at' => 'datetime',
            'void_at' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function receipt()
    {
        return $this->hasOne(PaymentReceipt::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function isPosted(): bool
    {
        return $this->status === 'POSTED';
    }
}
