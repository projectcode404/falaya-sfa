<?php

namespace App\Actions\Collection;

use App\DomainServices\OperationalDateService;
use App\Models\DailyCashReconciliation;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class ProcessCashReconciliationAction
{
    public function __construct(
        private readonly OperationalDateService $operationalDateService,
    ) {}

    public function execute(int $salesmanId, float $actualReceived, ?string $notes = null): DailyCashReconciliation
    {
        return DB::transaction(function () use ($salesmanId, $actualReceived, $notes) {
            $operationalDate = $this->operationalDateService->current()->toDateString();

            // Hitung cash sales hari ini
            $cashSalesTotal = SalesOrder::where('salesman_id', $salesmanId)
                ->where('operational_date', $operationalDate)
                ->where('payment_type', 'CASH')
                ->where('status', 'POSTED')
                ->sum('total_amount');

            // Hitung collection cash hari ini
            $collectionCashTotal = Payment::where('collected_by', $salesmanId)
                ->where('operational_date', $operationalDate)
                ->where('payment_method', 'CASH')
                ->where('status', 'POSTED')
                ->sum('total_amount');

            $systemTotal = $cashSalesTotal + $collectionCashTotal;
            $difference = $actualReceived - $systemTotal;

            $threshold = (float) Setting::get('cash_reconciliation_threshold', 5000);
            $status = abs($difference) <= $threshold ? 'RECONCILED' : 'DISCREPANCY';

            $reconciliation = DailyCashReconciliation::updateOrCreate(
                ['salesman_id' => $salesmanId, 'operational_date' => $operationalDate],
                [
                    'cash_sales_total' => $cashSalesTotal,
                    'collection_cash_total' => $collectionCashTotal,
                    'system_total' => $systemTotal,
                    'actual_received' => $actualReceived,
                    'difference' => $difference,
                    'status' => $status,
                    'discrepancy_notes' => $status === 'DISCREPANCY' ? $notes : null,
                    'reconciled_by' => auth()->id(),
                    'reconciled_at' => now(),
                    'created_at' => now(),
                ]
            );

            return $reconciliation;
        });
    }
}
