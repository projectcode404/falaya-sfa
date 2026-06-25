<?php

namespace App\Actions\Visit;

use App\Models\VisitPlan;
use Illuminate\Support\Facades\DB;

class ManualUpdateVisitStatusAction
{
    private const ALLOWED_STATUSES = ['NO_ORDER', 'OUTLET_CLOSED', 'SKIPPED'];

    public function execute(VisitPlan $visitPlan, string $status): VisitPlan
    {
        if (! in_array($status, self::ALLOWED_STATUSES)) {
            throw new \LogicException("Status '{$status}' tidak diizinkan untuk update manual.");
        }

        return DB::transaction(function () use ($visitPlan, $status) {
            $visitPlan->update(['status' => $status]);

            return $visitPlan->fresh();
        });
    }
}
