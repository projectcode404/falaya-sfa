<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'holder_type',
        'holder_id',
        'condition',
        'qty',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'updated_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
