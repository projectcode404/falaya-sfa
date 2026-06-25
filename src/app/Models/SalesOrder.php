<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_number',
        'visit_plan_id',
        'customer_id',
        'salesman_id',
        'operational_date',
        'payment_type',
        'subtotal',
        'total_amount',
        'status',
        'receiver_name',
        'requires_override',
        'override_status',
        'idempotency_key',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'posted_by',
        'posted_at',
        'cancelled_by',
        'cancelled_at',
        'cancel_reason',
        'void_by',
        'void_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'subtotal' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'requires_override' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'posted_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'void_at' => 'datetime',
        ];
    }

    public function visitPlan()
    {
        return $this->belongsTo(VisitPlan::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function creditOverrideRequest()
    {
        return $this->hasOne(CreditOverrideRequest::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function isPosted(): bool
    {
        return $this->status === 'POSTED';
    }

    public function isCash(): bool
    {
        return $this->payment_type === 'CASH';
    }

    public function isCredit(): bool
    {
        return $this->payment_type === 'CREDIT';
    }
}
