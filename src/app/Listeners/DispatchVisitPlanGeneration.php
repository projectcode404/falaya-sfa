<?php

namespace App\Listeners;

use App\Events\DailyClosingExecuted;
use App\Jobs\GenerateVisitPlanForNewDayJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchVisitPlanGeneration implements ShouldQueue
{
    public function handle(DailyClosingExecuted $event): void
    {
        GenerateVisitPlanForNewDayJob::dispatch($event->newOperationalDate)
            ->onQueue('critical');
    }
}
