<?php

namespace App\Livewire\Pwa;

use App\DomainServices\OperationalDateService;
use App\Models\StockBalance as StockBalanceModel;
use App\Models\VisitPlan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(OperationalDateService $dateService)
    {
        $user = Auth::user();
        $today = $dateService->current();
        $stokBawaan = StockBalanceModel::with('product')
            ->where('holder_type', 'SALESMAN')
            ->where('holder_id', $user->id)
            ->where('condition', 'GOOD')
            ->where('qty', '>', 0)
            ->get();
        $totalVisit = VisitPlan::where('salesman_id', $user->id)
            ->where('operational_date', $today)
            ->where('is_planned', true)
            ->count();
        $visitSelesai = VisitPlan::where('salesman_id', $user->id)
            ->where('operational_date', $today)
            ->whereIn('status', ['COMPLETED', 'NO_ORDER', 'OUTLET_CLOSED'])
            ->count();
        $visitProgress = $totalVisit > 0
            ? (int) round($visitSelesai / $totalVisit * 100)
            : 0;
        $visitBelum = VisitPlan::where('salesman_id', $user->id)
            ->where('operational_date', $today)
            ->whereIn('status', ['PLANNED', 'IN_PROGRESS'])
            ->with('customer')
            ->get();

        return view('livewire.pwa.dashboard', [
            'namaUser' => $user->name,
            'today' => $today,
            'stokBawaan' => $stokBawaan,
            'totalVisit' => $totalVisit,
            'visitSelesai' => $visitSelesai,
            'visitProgress' => $visitProgress,
            'visitBelum' => $visitBelum,
        ])->layout('components.pwa.layout', ['title' => 'Dashboard']);
    }
}
