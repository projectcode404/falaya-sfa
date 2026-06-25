<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionTask extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'visit_plan_id',
        'customer_id',
        'salesman_id',
        'operational_date',
        'total_outstanding_snapshot',
        'priority',
        'result_notes',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'total_outstanding_snapshot' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function visitPlan()
    {
        return $this->belongsTo(VisitPlan::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
