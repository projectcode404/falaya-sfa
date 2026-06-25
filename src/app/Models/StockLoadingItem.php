<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLoadingItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stock_loading_id',
        'product_id',
        'qty',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
    ];

    public function stockLoading(): BelongsTo
    {
        return $this->belongsTo(StockLoading::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
