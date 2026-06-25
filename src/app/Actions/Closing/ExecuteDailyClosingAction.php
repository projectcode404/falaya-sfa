<?php

namespace App\Actions\Closing;

use App\DomainServices\OperationalDateService;
use App\Events\DailyClosingExecuted;
use App\Models\DailyClosing;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Support\Facades\DB;

class ExecuteDailyClosingAction
{
    public function __construct(
        private readonly OperationalDateService $operationalDateService,
        private readonly ValidateClosingChecklistAction $validateChecklist,
    ) {}

    public function execute(?string $notes = null): DailyClosing
    {
        $checklist = $this->validateChecklist->execute();

        if (! $checklist['can_close']) {
            throw new \LogicException('Closing tidak dapat dilakukan: '.count($checklist['errors']).' item belum selesai.');
        }

        $previousDate = $this->operationalDateService->current();
        $newDate = $previousDate->copy()->addDay();

        $dailyClosing = DB::transaction(function () use ($previousDate, $notes) {
            $date = $previousDate->toDateString();

            // Tandai Visit Plan tanpa realisasi → SKIPPED
            VisitPlan::where('operational_date', $date)
                ->where('status', 'PLANNED')
                ->update(['status' => 'SKIPPED']);

            $totalSalesmanActive = User::role('SALESMAN')
                ->where('is_active', true)
                ->count();

            $totalVisitSkipped = VisitPlan::where('operational_date', $date)
                ->where('status', 'SKIPPED')
                ->count();

            // Advance operational date
            $this->operationalDateService->advance();

            return DailyClosing::create([
                'operational_date' => $date,
                'closed_by' => auth()->id(),
                'closed_at' => now(),
                'total_salesman_active' => $totalSalesmanActive,
                'total_visit_skipped' => $totalVisitSkipped,
                'notes' => $notes,
            ]);
        });

        // Dispatch event SETELAH commit
        event(new DailyClosingExecuted($newDate, $previousDate));

        return $dailyClosing;
    }
}
