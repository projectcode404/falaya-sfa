<?php

namespace App\Actions\Inventory;

use App\DomainServices\DocumentNumberService;
use App\DomainServices\OperationalDateService;
use App\Models\StockLoading;
use App\Models\StockLoadingItem;
use Illuminate\Support\Facades\DB;

class CreateStockLoadingAction
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly OperationalDateService $operationalDateService,
    ) {}

    public function execute(int $salesmanId, array $items): StockLoading
    {
        return DB::transaction(function () use ($salesmanId, $items) {
            $documentNumber = $this->documentNumberService->generate('SL');

            $loading = StockLoading::create([
                'document_number' => $documentNumber,
                'salesman_id' => $salesmanId,
                'operational_date' => $this->operationalDateService->current()->toDateString(),
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            foreach ($items as $item) {
                StockLoadingItem::create([
                    'stock_loading_id' => $loading->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);
            }

            return $loading->load('items');
        });
    }
}
