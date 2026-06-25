<?php

namespace App\Actions\Inventory;

use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;

class RejectStockAdjustmentAction
{
    public function execute(StockAdjustment $adjustment, string $reason): StockAdjustment
    {
        if ($adjustment->status !== 'PENDING_APPROVAL') {
            throw new \LogicException('Hanya PENDING_APPROVAL yang bisa di-reject.');
        }

        return DB::transaction(function () use ($adjustment, $reason) {
            $adjustment->update([
                'status' => 'REJECTED',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'approval_notes' => $reason,
            ]);

            return $adjustment->fresh();
        });
    }
}
