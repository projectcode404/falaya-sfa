<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'invoice_number',
        'sales_order_id',
        'customer_id',
        'salesman_id',
        'customer_name_snapshot',
        'customer_address_snapshot',
        'receiver_name',
        'invoice_date',
        'due_date',
        'credit_term_days_snapshot',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'pdf_media_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'created_at' => 'datetime',
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

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'UNPAID';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'OVERDUE';
    }

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }
}
