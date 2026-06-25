<?php

namespace App\Actions\Visit;

use App\Models\VisitPlan;
use App\Models\VisitRealization;
use Illuminate\Support\Facades\DB;

class CheckoutVisitAction
{
    public function execute(VisitPlan $visitPlan, ?array $gpsData = null): VisitRealization
    {
        if ($visitPlan->status !== 'IN_PROGRESS') {
            throw new \LogicException('Visit Plan bukan dalam status IN_PROGRESS.');
        }

        $realization = $visitPlan->realization;
        if (! $realization) {
            throw new \LogicException('Visit Realization tidak ditemukan.');
        }

        return DB::transaction(function () use ($visitPlan, $realization, $gpsData) {
            $realization->update([
                'checkout_latitude' => $gpsData['latitude'] ?? null,
                'checkout_longitude' => $gpsData['longitude'] ?? null,
                'checkout_at' => now(),
            ]);

            // Status ditentukan oleh apakah ada Sales Order -- default NO_ORDER dulu
            // akan di-update ke COMPLETED saat Sales Order di-POST
            if ($visitPlan->status === 'IN_PROGRESS') {
                $visitPlan->update(['status' => 'NO_ORDER']);
            }

            return $realization->fresh();
        });
    }
}
