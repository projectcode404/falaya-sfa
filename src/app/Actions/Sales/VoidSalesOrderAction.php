<?php

namespace App\Actions\Sales;

use App\DomainServices\StockBalanceService;
use App\DomainServices\StockLedgerService;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockLedger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VoidSalesOrderAction
{
    public function __construct(
        private readonly StockBalanceService $stockBalanceService,
        private readonly StockLedgerService $stockLedgerService,
    ) {}

    public function execute(SalesOrder $salesOrder, string $reason): SalesOrder
    {
        if (! $salesOrder->isPosted()) {
            throw new \LogicException('Hanya POSTED yang bisa di-void.');
        }

        return DB::transaction(function () use ($salesOrder, $reason) {
            /** @var Collection<int, SalesOrderItem> $items */
            $items = $salesOrder->items()->with('product')->get();

            foreach ($items as $item) {
                $balance = $this->stockBalanceService->lockAndGetBalance(
                    $item->product_id,
                    'SALESMAN',
                    $salesOrder->salesman_id,
                    'GOOD'
                );
                $this->stockBalanceService->applyMovement($balance, (float) $item->qty);

                $originalLedger = StockLedger::where('source_type', 'SALE')
                    ->where('source_id', $salesOrder->id)
                    ->where('product_id', $item->product_id)
                    ->first();

                $this->stockLedgerService->recordMovement(
                    $item->product,
                    'SALESMAN',
                    $salesOrder->salesman_id,
                    'GOOD',
                    (float) $item->qty,
                    'SALE_REVERSAL',
                    $salesOrder->id,
                    $originalLedger?->id
                );
            }

            $salesOrder->update([
                'status' => 'VOID',
                'void_by' => auth()->id(),
                'void_at' => now(),
                'void_reason' => $reason,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            return $salesOrder->fresh();
        });
    }
}
