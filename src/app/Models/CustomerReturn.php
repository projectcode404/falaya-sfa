<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReturn extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_number',
        'invoice_id',
        'customer_id',
        'salesman_id',
        'reason',
        'photo_media_id',
        'total_amount',
        'refund_type',
        'status',
        'created_by',
        'created_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'approval_notes',
        'refund_processed_by',
        'refund_processed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'refund_processed_at' => 'datetime',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(CustomerReturnItem::class);
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'PENDING_APPROVAL';
    }
}
