<?php

namespace App\Actions\Inventory;

use App\DomainServices\StockBalanceService;
use App\DomainServices\StockLedgerService;
use App\Models\Product;
use App\Models\StockWriteoff;
use Illuminate\Support\Facades\DB;

class ApproveStockWriteoffAction
{
    public function __construct(
        private readonly StockBalanceService $stockBalanceService,
        private readonly StockLedgerService $stockLedgerService,
    ) {}

    public function execute(StockWriteoff $writeoff, ?string $notes = null): StockWriteoff
    {
        if ($writeoff->status !== 'PENDING_APPROVAL') {
            throw new \LogicException('Hanya PENDING_APPROVAL yang bisa di-approve.');
        }

        return DB::transaction(function () use ($writeoff, $notes) {
            $product = Product::findOrFail($writeoff->product_id);

            // Kurangi stok Gudang BAD
            $balance = $this->stockBalanceService->lockAndGetBalance(
                $writeoff->product_id,
                'WAREHOUSE',
                null,
                'BAD'
            );

            if (! $this->stockBalanceService->validateSufficientStock($balance, $writeoff->qty)) {
                throw new \RuntimeException(
                    'Stok Gudang BAD tidak cukup. '.
                    "Dibutuhkan: {$writeoff->qty}, Tersedia: {$balance->qty}"
                );
            }

            $this->stockBalanceService->applyMovement($balance, -$writeoff->qty);

            // Catat ledger
            $this->stockLedgerService->recordMovement(
                $product,
                'WAREHOUSE',
                null,
                'BAD',
                -$writeoff->qty,
                'WRITE_OFF',
                $writeoff->id,
                null,
                $writeoff->reason
            );

            $writeoff->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            return $writeoff->fresh();
        });
    }
}
