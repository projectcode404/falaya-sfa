<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditOverrideRequest extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sales_order_id',
        'customer_id',
        'outstanding_at_request',
        'order_amount',
        'credit_limit_at_request',
        'status',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'approval_notes',
    ];

    protected function casts(): array
    {
        return [
            'outstanding_at_request' => 'decimal:2',
            'order_amount' => 'decimal:2',
            'credit_limit_at_request' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
