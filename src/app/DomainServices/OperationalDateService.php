<?php

namespace App\DomainServices;

use App\Models\OperationalDate;
use Illuminate\Support\Carbon;

class OperationalDateService
{
    public function current(): Carbon
    {
        $record = OperationalDate::first();

        return Carbon::parse($record->current_date_value);
    }

    public function isSyncedWithCalendar(): bool
    {
        return $this->current()->isToday();
    }

    public function advance(): void
    {
        // HANYA dipanggil dari dalam ExecuteDailyClosingAction
        // TIDAK membuka transaction sendiri
        $record = OperationalDate::first();
        $newDate = Carbon::parse($record->current_date_value)->addDay()->toDateString();
        $record->update([
            'current_date_value' => $newDate,
            'updated_at' => now(),
        ]);
    }

    public function setClosingInProgress(bool $status): void
    {
        OperationalDate::first()->update([
            'is_closing_in_progress' => $status,
            'updated_at' => now(),
        ]);
    }
}
