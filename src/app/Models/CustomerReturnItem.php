<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReturnItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_return_id',
        'product_id',
        'qty',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'unit_price' => 'decimal:2',
        ];
    }

    public function customerReturn()
    {
        return $this->belongsTo(CustomerReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
