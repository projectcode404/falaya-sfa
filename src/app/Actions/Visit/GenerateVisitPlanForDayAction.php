<?php

namespace App\Actions\Visit;

use App\Models\VisitPlan;
use App\Models\VisitSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateVisitPlanForDayAction
{
    public function execute(VisitSchedule $schedule, Carbon $operationalDate): ?VisitPlan
    {
        return DB::transaction(function () use ($schedule, $operationalDate) {
            // Cek duplikasi -- unique constraint di DB sudah jaga ini,
            // tapi cek di aplikasi dulu untuk pesan error yang lebih jelas
            $existing = VisitPlan::where('customer_id', $schedule->customer_id)
                ->where('salesman_id', $schedule->salesman_id)
                ->where('operational_date', $operationalDate->toDateString())
                ->first();

            if ($existing) {
                return null; // Sudah ada, skip
            }

            return VisitPlan::create([
                'salesman_id' => $schedule->salesman_id,
                'customer_id' => $schedule->customer_id,
                'operational_date' => $operationalDate->toDateString(),
                'is_planned' => true,
                'area_id_snapshot' => $schedule->customer->area_id,
                'visit_schedule_id' => $schedule->id,
                'status' => 'PLANNED',
                'created_by' => null, // auto-generate sistem
                'created_at' => now(),
            ]);
        });
    }
}
