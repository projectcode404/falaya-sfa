<?php

namespace App\Actions\Inventory;

use App\Models\StockLoading;
use Illuminate\Support\Facades\DB;

class CancelStockLoadingAction
{
    public function execute(StockLoading $loading, string $reason): StockLoading
    {
        if ($loading->status !== 'DRAFT') {
            throw new \LogicException('Hanya DRAFT yang bisa di-cancel.');
        }

        return DB::transaction(function () use ($loading, $reason) {
            $loading->update([
                'status' => 'CANCELLED',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            return $loading->fresh();
        });
    }
}
