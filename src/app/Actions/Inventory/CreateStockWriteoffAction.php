<?php

namespace App\Actions\Inventory;

use App\DomainServices\DocumentNumberService;
use App\Models\StockWriteoff;
use Illuminate\Support\Facades\DB;

class CreateStockWriteoffAction
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
    ) {}

    public function execute(int $productId, float $qty, string $reason): StockWriteoff
    {
        return DB::transaction(function () use ($productId, $qty, $reason) {
            $documentNumber = $this->documentNumberService->generate('WO');

            return StockWriteoff::create([
                'document_number' => $documentNumber,
                'product_id' => $productId,
                'qty' => $qty,
                'reason' => $reason,
                'status' => 'PENDING_APPROVAL',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }
}
