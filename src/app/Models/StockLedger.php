<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'holder_type',
        'holder_id',
        'condition',
        'qty',
        'operational_date',
        'source_type',
        'source_id',
        'reference_ledger_id',
        'notes',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'operational_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    // Guard append-only -- StockLedger tidak boleh diupdate
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \LogicException('StockLedger is append-only. Use create() for reversal entries.');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function referenceLedger()
    {
        return $this->belongsTo(StockLedger::class, 'reference_ledger_id');
    }
}
