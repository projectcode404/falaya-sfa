<?php

namespace App\Actions\Visit;

use App\Models\Customer;
use App\Models\VisitPlan;
use Illuminate\Support\Facades\DB;

class CreateUnplannedVisitAction
{
    public function execute(int $salesmanId, int $customerId, string $operationalDate): VisitPlan
    {
        return DB::transaction(function () use ($salesmanId, $customerId, $operationalDate) {
            $customer = Customer::findOrFail($customerId);

            return VisitPlan::create([
                'salesman_id' => $salesmanId,
                'customer_id' => $customerId,
                'operational_date' => $operationalDate,
                'is_planned' => false,
                'area_id_snapshot' => $customer->area_id,
                'visit_schedule_id' => null,
                'status' => 'PLANNED',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }
}
