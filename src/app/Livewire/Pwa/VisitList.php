<?php

namespace App\Livewire\Pwa;

use App\DomainServices\OperationalDateService;
use App\Models\Invoice;
use App\Models\VisitPlan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VisitList extends Component
{
    public function render(OperationalDateService $dateService)
    {
        $user = Auth::user();
        $today = $dateService->current();

        $rawVisits = VisitPlan::where('salesman_id', $user->id)
            ->where('operational_date', $today)
            ->with(['customer.area'])
            ->orderByRaw("CASE status
                WHEN 'IN_PROGRESS'   THEN 1
                WHEN 'PLANNED'       THEN 2
                WHEN 'COMPLETED'     THEN 3
                WHEN 'NO_ORDER'      THEN 4
                WHEN 'OUTLET_CLOSED' THEN 5
                WHEN 'SKIPPED'       THEN 6
                ELSE 7 END")
            ->get();

        $visits = $rawVisits->map(fn ($v) => [
            'id' => $v->id,
            'customer_name' => $v->customer->customer_name ?? '-',
            'area_name' => $v->customer->area->area_name ?? '-',
            'status' => $v->status,
            'has_outstanding' => Invoice::where('customer_id', $v->customer_id)
                ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
                ->exists(),
        ])->values();

        return view('livewire.pwa.visit-list', [
            'visits' => $visits,
            'today' => $today->translatedFormat('l, d F Y'),
        ])->layout('components.pwa.layout', ['title' => 'Kunjungan Hari Ini']);
    }
}
