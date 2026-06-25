<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReceipt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'receipt_number',
        'payment_id',
        'customer_id',
        'customer_name_snapshot',
        'collector_name_snapshot',
        'total_paid',
        'remaining_after',
        'receipt_date',
        'qr_payload',
        'pdf_media_id',
        'downloaded_at',
        'status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'total_paid' => 'decimal:2',
            'remaining_after' => 'decimal:2',
            'receipt_date' => 'date',
            'downloaded_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
