<?php

namespace App\Actions\VisitSchedule;

use App\Models\VisitSchedule;
use Illuminate\Support\Facades\DB;

class DeactivateVisitScheduleAction
{
    public function execute(VisitSchedule $schedule): VisitSchedule
    {
        return DB::transaction(function () use ($schedule) {
            $schedule->update([
                'is_active' => false,
                'effective_to' => now()->toDateString(),
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            return $schedule->fresh();
        });
    }
}
