<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_number',
        'product_id',
        'holder_type',
        'holder_id',
        'qty',
        'reason',
        'notes',
        'source_context',
        'status',
        'created_by',
        'created_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'approval_notes',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'created_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'PENDING_APPROVAL');
    }

    /**
     * Holder ini sengaja polymorphic manual (holder_type + holder_id),
     * bukan Eloquent morphTo, karena holder_type merujuk ke KONSEP
     * (WAREHOUSE / SALESMAN) bukan ke Model class berbeda -- WAREHOUSE
     * tidak punya tabel sendiri (holder_id selalu NULL untuk WAREHOUSE).
     */
    public function isWarehouseHolder(): bool
    {
        return $this->holder_type === 'WAREHOUSE';
    }

    public function isSalesmanHolder(): bool
    {
        return $this->holder_type === 'SALESMAN';
    }
}
