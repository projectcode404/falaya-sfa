<?php

namespace Database\Seeders;

use App\Actions\Visit\GenerateVisitPlanForDayAction;
use App\Models\VisitPlan;
use App\Models\VisitRealization;
use App\Models\VisitSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DemoOperationalSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        // 1. Reset operational date ke hari ini
        DB::table('operational_dates')->update([
            'current_date_value' => $today->toDateString(),
            'is_closing_in_progress' => false,
            'updated_at' => now(),
        ]);

        $this->command->info('Operational date reset to: '.$today->toDateString());

        // 2. Hapus visit plan hari ini beserta realization-nya
        $existingPlanIds = VisitPlan::where('operational_date', $today->toDateString())
            ->pluck('id');

        if ($existingPlanIds->isNotEmpty()) {
            VisitRealization::whereIn('visit_plan_id', $existingPlanIds)->delete();
            VisitPlan::whereIn('id', $existingPlanIds)->delete();
            $this->command->info('Deleted '.$existingPlanIds->count().' existing visit plans and their realizations.');
        }

        // 3. Generate visit plan untuk hari ini berdasarkan day_of_week
        $dayOfWeek = $today->dayOfWeekIso; // 1=Senin ... 7=Minggu
        $this->command->info('Day of week (ISO): '.$dayOfWeek);

        $schedules = VisitSchedule::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('effective_from', '<=', $today->toDateString())
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $today->toDateString());
            })
            ->get();

        $this->command->info('Schedules found for today: '.$schedules->count());

        if ($schedules->isEmpty()) {
            $this->command->warn('No schedules for day_of_week='.$dayOfWeek.'. Check VisitSchedule data.');
            $this->command->info('Available day_of_week values: '.
                VisitSchedule::where('is_active', true)->pluck('day_of_week')->unique()->sort()->join(', '));

            return;
        }

        $action = app(GenerateVisitPlanForDayAction::class);

        foreach ($schedules as $schedule) {
            $action->execute($schedule, $today);
        }

        $count = VisitPlan::where('operational_date', $today->toDateString())->count();
        $this->command->info('Visit plans generated: '.$count);
    }
}
