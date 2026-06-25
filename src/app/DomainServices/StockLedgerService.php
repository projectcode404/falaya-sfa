<?php

namespace App\DomainServices;

use App\Models\Product;
use App\Models\StockLedger;

class StockLedgerService
{
    public function __construct(
        private readonly OperationalDateService $operationalDateService
    ) {}

    public function recordMovement(
        Product $product,
        string $holderType,
        ?int $holderId,
        string $condition,
        float $qty,
        string $sourceType,
        int $sourceId,
        ?int $referenceLedgerId = null,
        ?string $notes = null
    ): StockLedger {
        // Murni INSERT -- TIDAK membuka transaction sendiri
        // TIDAK melakukan locking -- locking adalah tanggung jawab StockBalanceService
        return StockLedger::create([
            'product_id' => $product->id,
            'holder_type' => $holderType,
            'holder_id' => $holderId,
            'condition' => $condition,
            'qty' => $qty,
            'operational_date' => $this->operationalDateService->current()->toDateString(),
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'reference_ledger_id' => $referenceLedgerId,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }
}
