<?php

namespace App\Jobs;

use App\Actions\Visit\GenerateVisitPlanForDayAction;
use App\Models\VisitPlan;
use App\Models\VisitSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateVisitPlanForNewDayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(public readonly Carbon $operationalDate) {}

    public function handle(GenerateVisitPlanForDayAction $action): void
    {
        // Idempotent guard
        $alreadyGenerated = VisitPlan::where('operational_date', $this->operationalDate->toDateString())
            ->where('is_planned', true)
            ->exists();

        if ($alreadyGenerated) {
            return;
        }

        $dayOfWeek = $this->operationalDate->dayOfWeekIso; // 1=Senin ... 7=Minggu

        $schedules = VisitSchedule::with('customer')
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('effective_from', '<=', $this->operationalDate->toDateString())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $this->operationalDate->toDateString());
            })
            ->get();

        foreach ($schedules as $schedule) {
            $action->execute($schedule, $this->operationalDate);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('Gagal generate Visit Plan untuk hari baru', [
            'operational_date' => $this->operationalDate->toDateString(),
            'error' => $exception->getMessage(),
        ]);
    }
}
