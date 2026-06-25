<?php

namespace App\Livewire\Pwa;

use App\DomainServices\OperationalDateService;
use App\Models\VisitPlan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VisitList extends Component
{
    public function render(OperationalDateService $dateService)
    {
        $user = Auth::user();
        $today = $dateService->current();
        $visits = VisitPlan::where('salesman_id', $user->id)
            ->where('operational_date', $today)
            ->with(['customer', 'salesOrders' => fn ($q) => $q->where('status', 'POSTED')])
            ->orderByRaw("CASE status
                WHEN 'IN_PROGRESS' THEN 1
                WHEN 'PLANNED' THEN 2
                WHEN 'COMPLETED' THEN 3
                WHEN 'NO_ORDER' THEN 4
                WHEN 'OUTLET_CLOSED' THEN 5
                WHEN 'SKIPPED' THEN 6
                ELSE 7 END")
            ->get();

        return view('livewire.pwa.visit-list', [
            'visits' => $visits,
            'today' => $today,
        ])->layout('components.pwa.layout', ['title' => 'Kunjungan Hari Ini']);
    }
}
