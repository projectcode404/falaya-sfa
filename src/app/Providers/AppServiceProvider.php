<?php

namespace App\Providers;

use App\Events\DailyClosingExecuted;
use App\Listeners\DispatchVisitPlanGeneration;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(DailyClosingExecuted::class, DispatchVisitPlanGeneration::class);
    }
}
