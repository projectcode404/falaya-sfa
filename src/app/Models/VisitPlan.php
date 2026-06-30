<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property Customer $customer
 * @property VisitRealization|null $realization
 */
class VisitPlan extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'salesman_id',
        'customer_id',
        'operational_date',
        'is_planned',
        'area_id_snapshot',
        'visit_schedule_id',
        'status',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'is_planned' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function collectionTask()
    {
        return $this->hasOne(CollectionTask::class, 'visit_plan_id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class, 'visit_plan_id');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id_snapshot');
    }

    public function visitSchedule()
    {
        return $this->belongsTo(VisitSchedule::class);
    }

    public function realization()
    {
        return $this->hasOne(VisitRealization::class);
    }

    public function isPlanned(): bool
    {
        return $this->is_planned === true;
    }

    public function isInProgress(): bool
    {
        return $this->status === 'IN_PROGRESS';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['COMPLETED', 'NO_ORDER', 'OUTLET_CLOSED']);
    }

    public function scopeForSalesmanOnDate($query, int $salesmanId, string $date)
    {
        return $query->where('salesman_id', $salesmanId)
            ->where('operational_date', $date);
    }
}
