<?php

namespace App\Actions\Closing;

use App\DomainServices\OperationalDateService;
use App\Models\CollectionTask;
use App\Models\SalesOrder;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\VisitPlan;

class ValidateClosingChecklistAction
{
    public function __construct(
        private readonly OperationalDateService $operationalDateService,
    ) {}

    public function execute(): array
    {
        $date = $this->operationalDateService->current()->toDateString();
        $errors = [];

        // 1. Tidak ada Visit IN_PROGRESS
        $inProgressVisits = VisitPlan::where('operational_date', $date)
            ->where('status', 'IN_PROGRESS')
            ->with('salesman', 'customer')
            ->get();

        foreach ($inProgressVisits as $visit) {
            $errors[] = [
                'type' => 'visit_in_progress',
                'message' => "Visit IN_PROGRESS: {$visit->salesman->name} → {$visit->customer->customer_name}",
                'visit_plan_id' => $visit->id,
            ];
        }

        // 2. Tidak ada Sales Order DRAFT
        $draftOrders = SalesOrder::where('operational_date', $date)
            ->where('status', 'DRAFT')
            ->with('salesman', 'customer')
            ->get();

        foreach ($draftOrders as $so) {
            $errors[] = [
                'type' => 'sales_order_draft',
                'message' => "Sales Order DRAFT: {$so->document_number} ({$so->salesman->name})",
                'sales_order_id' => $so->id,
            ];
        }

        // 3. Tidak ada stok salesman tersisa (qty > 0)
        $salesmanStocks = StockBalance::where('holder_type', 'SALESMAN')
            ->where('condition', 'GOOD')
            ->where('qty', '>', 0)
            ->with('product')
            ->get();

        foreach ($salesmanStocks as $stock) {
            $errors[] = [
                'type' => 'stock_not_unloaded',
                'message' => "Stok belum di-unloading: {$stock->product->product_name} ({$stock->qty} pcs) — Salesman ID {$stock->holder_id}",
                'stock_balance_id' => $stock->id,
            ];
        }

        // 4. Tidak ada Collection Task PLANNED tanpa hasil
        $plannedTasks = CollectionTask::where('operational_date', $date)
            ->where('status', 'PLANNED')
            ->with('salesman', 'customer')
            ->get();

        foreach ($plannedTasks as $task) {
            $errors[] = [
                'type' => 'collection_task_pending',
                'message' => "Collection Task belum diselesaikan: {$task->customer->customer_name} ({$task->salesman->name})",
                'collection_task_id' => $task->id,
            ];
        }

        // 5. Cash Reconciliation sudah RECONCILED untuk salesman yang punya payment hari ini
        $unreconciledSalesman = User::role('SALESMAN')
            ->where('is_active', true)
            ->whereDoesntHave('cashReconciliations', function ($q) use ($date) {
                $q->where('operational_date', $date)
                    ->where('status', 'RECONCILED');
            })
            ->whereHas('payments', function ($q) use ($date) {
                $q->where('operational_date', $date)
                    ->where('status', 'POSTED');
            })
            ->get();

        foreach ($unreconciledSalesman as $salesman) {
            $errors[] = [
                'type' => 'cash_not_reconciled',
                'message' => "Cash Reconciliation belum selesai: {$salesman->name}",
                'salesman_id' => $salesman->id,
            ];
        }

        return [
            'can_close' => empty($errors),
            'errors' => $errors,
            'date' => $date,
        ];
    }
}
