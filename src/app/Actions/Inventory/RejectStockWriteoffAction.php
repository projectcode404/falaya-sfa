<?php

namespace App\Actions\Inventory;

use App\Models\StockWriteoff;

class RejectStockWriteoffAction
{
    public function execute(StockWriteoff $writeoff, string $notes): void
    {
        $writeoff->update([
            'status' => 'REJECTED',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'approval_notes' => $notes,
        ]);
    }
}
