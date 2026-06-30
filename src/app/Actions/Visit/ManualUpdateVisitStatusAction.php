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

        // NO_ORDER dan OUTLET_CLOSED secara semantik berarti "tidak ada
        // transaksi terjadi" -- kontradiktif bila visit ini sudah punya
        // Sales Order POSTED. Tolak transisi ini agar data tetap konsisten
        // (lihat juga CheckoutVisitAction yang menentukan COMPLETED vs
        // NO_ORDER berdasarkan keberadaan SO POSTED).
        if (in_array($status, ['NO_ORDER', 'OUTLET_CLOSED'])) {
            $hasPostedOrder = $visitPlan->salesOrders()
                ->where('status', 'POSTED')
                ->exists();

            if ($hasPostedOrder) {
                throw new \LogicException(
                    'Kunjungan ini sudah memiliki pesanan. Gunakan Check-out untuk menyelesaikan kunjungan.'
                );
            }
        }

        return DB::transaction(function () use ($visitPlan, $status) {
            $visitPlan->update(['status' => $status]);

            return $visitPlan->fresh();
        });
    }
}
