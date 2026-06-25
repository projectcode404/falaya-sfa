<?php

namespace App\Actions\Sales;

use App\DomainServices\OperationalDateService;
use App\DomainServices\StockBalanceService;
use App\DomainServices\StockLedgerService;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockLedger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CancelSalesOrderAction
{
    public function __construct(
        private readonly StockBalanceService $stockBalanceService,
        private readonly StockLedgerService $stockLedgerService,
        private readonly OperationalDateService $operationalDateService,
    ) {}

    public function execute(SalesOrder $salesOrder, string $reason): SalesOrder
    {
        if ($salesOrder->isDraft()) {
            return DB::transaction(function () use ($salesOrder, $reason) {
                $salesOrder->update([
                    'status' => 'CANCELLED',
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now(),
                    'cancel_reason' => $reason,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

                return $salesOrder->fresh();
            });
        }

        if (! $salesOrder->isPosted()) {
            throw new \LogicException('Hanya DRAFT atau POSTED yang bisa di-cancel.');
        }

        $currentDate = $this->operationalDateService->current()->toDateString();
        $soDate = $salesOrder->operational_date instanceof Carbon
            ? $salesOrder->operational_date->toDateString()
            : (string) $salesOrder->operational_date;

        if ($soDate !== $currentDate) {
            throw new \LogicException('Sales Order POSTED hanya bisa di-cancel pada hari operasional yang sama.');
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
                'status' => 'CANCELLED',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            $salesOrder->visitPlan->update(['status' => 'IN_PROGRESS']);

            return $salesOrder->fresh();
        });
    }
}
