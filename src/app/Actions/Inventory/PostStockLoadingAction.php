<?php

namespace App\Actions\Inventory;

use App\DomainServices\StockBalanceService;
use App\DomainServices\StockLedgerService;
use App\Models\StockLoading;
use App\Models\StockLoadingItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PostStockLoadingAction
{
    public function __construct(
        private readonly StockBalanceService $stockBalanceService,
        private readonly StockLedgerService $stockLedgerService,
    ) {}

    public function execute(StockLoading $loading): StockLoading
    {
        if ($loading->status !== 'DRAFT') {
            throw new \LogicException('Hanya DRAFT yang bisa di-POST.');
        }

        return DB::transaction(function () use ($loading) {
            /** @var Collection<int, StockLoadingItem> $items */
            $items = $loading->items()->with('product')->get();

            $balances = [];
            foreach ($items as $item) {
                $balance = $this->stockBalanceService->lockAndGetBalance(
                    $item->product_id,
                    'WAREHOUSE',
                    null,
                    'GOOD'
                );

                if (! $this->stockBalanceService->validateSufficientStock($balance, (float) $item->qty)) {
                    throw new \RuntimeException(
                        "Stok gudang tidak cukup untuk produk {$item->product->product_name}. ".
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

                $salesmanBalance = $this->stockBalanceService->lockAndGetBalance(
                    $item->product_id,
                    'SALESMAN',
                    $loading->salesman_id,
                    'GOOD'
                );
                $this->stockBalanceService->applyMovement($salesmanBalance, (float) $item->qty);

                $this->stockLedgerService->recordMovement(
                    $item->product,
                    'WAREHOUSE',
                    null,
                    'GOOD',
                    -(float) $item->qty,
                    'LOADING',
                    $loading->id
                );

                $this->stockLedgerService->recordMovement(
                    $item->product,
                    'SALESMAN',
                    $loading->salesman_id,
                    'GOOD',
                    (float) $item->qty,
                    'LOADING',
                    $loading->id
                );
            }

            $loading->update([
                'status' => 'POSTED',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            return $loading->fresh();
        });
    }
}
