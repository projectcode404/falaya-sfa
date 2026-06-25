<?php

namespace App\Actions\Inventory;

use App\DomainServices\StockBalanceService;
use App\DomainServices\StockLedgerService;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;

class ApproveStockAdjustmentAction
{
    public function __construct(
        private readonly StockBalanceService $stockBalanceService,
        private readonly StockLedgerService $stockLedgerService,
    ) {}

    public function execute(StockAdjustment $adjustment, ?string $notes = null): StockAdjustment
    {
        if ($adjustment->status !== 'PENDING_APPROVAL') {
            throw new \LogicException('Hanya PENDING_APPROVAL yang bisa di-approve.');
        }

        return DB::transaction(function () use ($adjustment, $notes) {
            $product = Product::findOrFail($adjustment->product_id);

            // Kurangi stok holder asal (GOOD)
            $sourceBalance = $this->stockBalanceService->lockAndGetBalance(
                $adjustment->product_id,
                $adjustment->holder_type,
                $adjustment->holder_id,
                'GOOD'
            );

            if (! $this->stockBalanceService->validateSufficientStock($sourceBalance, $adjustment->qty)) {
                throw new \RuntimeException(
                    'Stok tidak cukup untuk adjustment. '.
                    "Dibutuhkan: {$adjustment->qty}, Tersedia: {$sourceBalance->qty}"
                );
            }

            $this->stockBalanceService->applyMovement($sourceBalance, -$adjustment->qty);

            // Tambah stok Gudang BAD
            $warehouseBadBalance = $this->stockBalanceService->lockAndGetBalance(
                $adjustment->product_id,
                'WAREHOUSE',
                null,
                'BAD'
            );
            $this->stockBalanceService->applyMovement($warehouseBadBalance, $adjustment->qty);

            // Catat ledger
            $this->stockLedgerService->recordMovement(
                $product,
                $adjustment->holder_type,
                $adjustment->holder_id,
                'GOOD',
                -$adjustment->qty,
                'ADJUSTMENT',
                $adjustment->id,
                null,
                $adjustment->reason
            );

            $this->stockLedgerService->recordMovement(
                $product,
                'WAREHOUSE',
                null,
                'BAD',
                $adjustment->qty,
                'ADJUSTMENT',
                $adjustment->id,
                null,
                $adjustment->reason
            );

            $adjustment->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            return $adjustment->fresh();
        });
    }
}
