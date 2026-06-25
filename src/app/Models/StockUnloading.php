<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockUnloading extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_number',
        'salesman_id',
        'operational_date',
        'status',
        'created_by',
        'created_at',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'operational_date' => 'date',
        'created_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockUnloadingItem::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'DRAFT');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'POSTED');
    }
}
