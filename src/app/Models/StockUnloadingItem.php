<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockUnloadingItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stock_unloading_id',
        'product_id',
        'qty',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
    ];

    public function stockUnloading(): BelongsTo
    {
        return $this->belongsTo(StockUnloading::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
