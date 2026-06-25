<?php

namespace App\Actions\Inventory;

use App\DomainServices\StockBalanceService;
use App\DomainServices\StockLedgerService;
use App\Models\StockUnloading;
use App\Models\StockUnloadingItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PostStockUnloadingAction
{
    public function __construct(
        private readonly StockBalanceService $stockBalanceService,
        private readonly StockLedgerService $stockLedgerService,
    ) {}

    public function execute(StockUnloading $unloading): StockUnloading
    {
        if ($unloading->status !== 'DRAFT') {
            throw new \LogicException('Hanya DRAFT yang bisa di-POST.');
        }

        return DB::transaction(function () use ($unloading) {
            /** @var Collection<int, StockUnloadingItem> $items */
            $items = $unloading->items()->with('product')->get();

            $balances = [];
            foreach ($items as $item) {
                $balance = $this->stockBalanceService->lockAndGetBalance(
                    $item->product_id,
                    'SALESMAN',
                    $unloading->salesman_id,
                    'GOOD'
                );

                if (! $this->stockBalanceService->validateSufficientStock($balance, (float) $item->qty)) {
                    throw new \RuntimeException(
                        "Stok salesman tidak cukup untuk produk {$item->product->product_name}. ".
                        "Dibutuhkan: {$item->qty}, Tersedia: {$balance->qty}"
                    );
                }

                $balances[$item->product_id] = $balance;
            }

            foreach ($items as $item) {
                $this->stockBalanceService->applyMovement(
                    $balances[$item->product_id],
                    -(float) $item->qty
                );

                $warehouseBalance = $this->stockBalanceService->lockAndGetBalance(
                    $item->product_id,
                    'WAREHOUSE',
                    null,
                    'GOOD'
                );
                $this->stockBalanceService->applyMovement($warehouseBalance, (float) $item->qty);

                $this->stockLedgerService->recordMovement(
                    $item->product,
                    'SALESMAN',
                    $unloading->salesman_id,
                    'GOOD',
                    -(float) $item->qty,
                    'UNLOADING',
                    $unloading->id
                );

                $this->stockLedgerService->recordMovement(
                    $item->product,
                    'WAREHOUSE',
                    null,
                    'GOOD',
                    (float) $item->qty,
                    'UNLOADING',
                    $unloading->id
                );
            }

            $unloading->update([
                'status' => 'POSTED',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            return $unloading->fresh();
        });
    }
}
