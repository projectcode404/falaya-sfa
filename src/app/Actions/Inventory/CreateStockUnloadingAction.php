<?php

namespace App\Actions\Inventory;

use App\DomainServices\DocumentNumberService;
use App\DomainServices\OperationalDateService;
use App\Models\StockUnloading;
use App\Models\StockUnloadingItem;
use Illuminate\Support\Facades\DB;

class CreateStockUnloadingAction
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly OperationalDateService $operationalDateService,
    ) {}

    public function execute(int $salesmanId, array $items): StockUnloading
    {
        return DB::transaction(function () use ($salesmanId, $items) {
            $documentNumber = $this->documentNumberService->generate('SU');

            $unloading = StockUnloading::create([
                'document_number' => $documentNumber,
                'salesman_id' => $salesmanId,
                'operational_date' => $this->operationalDateService->current()->toDateString(),
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            foreach ($items as $item) {
                StockUnloadingItem::create([
                    'stock_unloading_id' => $unloading->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);
            }

            return $unloading->load('items');
        });
    }
}
