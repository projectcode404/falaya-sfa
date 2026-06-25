<?php

namespace App\Actions\Inventory;

use App\DomainServices\DocumentNumberService;
use App\DomainServices\OperationalDateService;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;

class CreateStockAdjustmentAction
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly OperationalDateService $operationalDateService,
    ) {}

    public function execute(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {
            $documentNumber = $this->documentNumberService->generate('SA');

            return StockAdjustment::create([
                'document_number' => $documentNumber,
                'product_id' => $data['product_id'],
                'holder_type' => $data['holder_type'],
                'holder_id' => $data['holder_id'] ?? null,
                'qty' => $data['qty'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'source_context' => $data['source_context'],
                'status' => 'PENDING_APPROVAL',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }
}
