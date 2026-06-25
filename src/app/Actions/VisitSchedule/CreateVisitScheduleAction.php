<?php

namespace App\Actions\VisitSchedule;

use App\Models\VisitSchedule;
use Illuminate\Support\Facades\DB;

class CreateVisitScheduleAction
{
    public function execute(array $data): VisitSchedule
    {
        return DB::transaction(function () use ($data) {
            return VisitSchedule::create([
                'salesman_id' => $data['salesman_id'],
                'customer_id' => $data['customer_id'],
                'day_of_week' => $data['day_of_week'],
                'is_active' => $data['is_active'] ?? true,
                'effective_from' => $data['effective_from'],
                'effective_to' => $data['effective_to'] ?? null,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }
}
