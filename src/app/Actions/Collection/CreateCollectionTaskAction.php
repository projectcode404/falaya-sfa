<?php

namespace App\Actions\Collection;

use App\DomainServices\OperationalDateService;
use App\Models\CollectionTask;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class CreateCollectionTaskAction
{
    public function __construct(
        private readonly OperationalDateService $operationalDateService,
    ) {}

    public function execute(int $customerId, int $salesmanId, ?int $visitPlanId = null): ?CollectionTask
    {
        return DB::transaction(function () use ($customerId, $salesmanId, $visitPlanId) {
            // Hitung outstanding
            $outstanding = Invoice::where('customer_id', $customerId)
                ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
                ->sum('remaining_amount');

            if ($outstanding <= 0) {
                return null; // Tidak perlu collection task
            }

            // Tentukan priority
            $dueSoonDays = (int) Setting::get('collection_due_soon_days', 3);
            $today = $this->operationalDateService->current();

            $hasOverdue = Invoice::where('customer_id', $customerId)
                ->where('status', 'OVERDUE')
                ->exists();

            $hasDueSoon = Invoice::where('customer_id', $customerId)
                ->whereIn('status', ['UNPAID', 'PARTIAL'])
                ->whereBetween('due_date', [
                    $today->toDateString(),
                    $today->addDays($dueSoonDays)->toDateString(),
                ])
                ->exists();

            $priority = $hasOverdue ? 'OVERDUE' : ($hasDueSoon ? 'DUE_SOON' : 'NORMAL');

            return CollectionTask::create([
                'visit_plan_id' => $visitPlanId,
                'customer_id' => $customerId,
                'salesman_id' => $salesmanId,
                'operational_date' => $this->operationalDateService->current()->toDateString(),
                'total_outstanding_snapshot' => $outstanding,
                'priority' => $priority,
                'status' => 'PLANNED',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }
}
