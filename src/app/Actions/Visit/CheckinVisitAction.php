<?php

namespace App\Actions\Visit;

use App\Models\Setting;
use App\Models\VisitPlan;
use App\Models\VisitRealization;
use App\Support\IdempotencyGuard;
use Illuminate\Support\Facades\DB;

class CheckinVisitAction
{
    public function __construct(
        private readonly IdempotencyGuard $idempotencyGuard,
    ) {}

    public function execute(VisitPlan $visitPlan, array $gpsData, string $idempotencyKey): VisitRealization
    {
        // Idempotency check
        $existing = $this->idempotencyGuard->checkOrRegister($idempotencyKey, VisitRealization::class);
        if ($existing instanceof VisitRealization) {
            return $existing;
        }

        if ($visitPlan->status !== 'PLANNED') {
            throw new \LogicException('Visit Plan bukan dalam status PLANNED.');
        }

        // GPS validation
        $gpsUnavailable = $gpsData['unavailable'] ?? false;

        if (! $gpsUnavailable) {
            $latitude = $gpsData['latitude'];
            $longitude = $gpsData['longitude'];
            $accuracy = $gpsData['accuracy'] ?? 0;

            // Hitung jarak ke outlet
            $customer = $visitPlan->customer;
            if ($customer->latitude && $customer->longitude) {
                $distance = $this->calculateDistance(
                    $latitude, $longitude,
                    (float) $customer->latitude,
                    (float) $customer->longitude
                );

                $radius = $customer->radius_tolerance_meter
                    ?? (int) Setting::get('default_radius_tolerance_meter', 100);

                if ($distance > $radius) {
                    throw new \RuntimeException(
                        "Jarak {$distance}m dari outlet, melebihi radius {$radius}m yang diizinkan."
                    );
                }
            }

            $lowAccuracyThreshold = (int) Setting::get('gps_low_accuracy_threshold_meter', 100);
            $lowAccuracy = $accuracy > $lowAccuracyThreshold;
        }

        return DB::transaction(function () use ($visitPlan, $gpsData, $idempotencyKey, $gpsUnavailable) {
            $lowAccuracy = false;
            if (! $gpsUnavailable) {
                $accuracy = $gpsData['accuracy'] ?? 0;
                $lowAccuracyThreshold = (int) Setting::get('gps_low_accuracy_threshold_meter', 100);
                $lowAccuracy = $accuracy > $lowAccuracyThreshold;
            }

            $realization = VisitRealization::create([
                'visit_plan_id' => $visitPlan->id,
                'checkin_latitude' => $gpsUnavailable ? null : $gpsData['latitude'],
                'checkin_longitude' => $gpsUnavailable ? null : $gpsData['longitude'],
                'checkin_accuracy_meter' => $gpsUnavailable ? null : ($gpsData['accuracy'] ?? null),
                'checkin_at' => now(),
                'gps_unavailable' => $gpsUnavailable,
                'gps_low_accuracy' => $lowAccuracy,
                'idempotency_key' => $idempotencyKey,
                'created_at' => now(),
            ]);

            $visitPlan->update(['status' => 'IN_PROGRESS']);

            return $realization;
        });
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c);
    }
}
