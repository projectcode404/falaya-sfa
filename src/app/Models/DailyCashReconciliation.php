<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyCashReconciliation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'salesman_id',
        'operational_date',
        'cash_sales_total',
        'collection_cash_total',
        'system_total',
        'actual_received',
        'difference',
        'status',
        'discrepancy_notes',
        'reconciled_by',
        'reconciled_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'cash_sales_total' => 'decimal:2',
            'collection_cash_total' => 'decimal:2',
            'system_total' => 'decimal:2',
            'actual_received' => 'decimal:2',
            'difference' => 'decimal:2',
            'reconciled_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
