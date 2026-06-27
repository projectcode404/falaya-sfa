<?php

namespace App\Livewire\Pwa;

use App\DomainServices\OperationalDateService;
use App\Models\Invoice;
use App\Models\SalesOrder;
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

        // Stock bawaan salesman
        $stokBawaan = StockBalanceModel::with('product')
            ->where('holder_type', 'SALESMAN')
            ->where('holder_id', $user->id)
            ->where('condition', 'GOOD')
            ->where('qty', '>', 0)
            ->get();

        $stockItems = $stokBawaan->map(fn ($s) => [
            'name' => $s->product->product_name ?? '-',
            'variant' => $s->product->variant ?? '',
            'qty' => (int) $s->qty,
        ])->values()->toArray();

        $totalStockQty = $stokBawaan->sum('qty');

        // Visit stats
        $visitTotal = VisitPlan::where('salesman_id', $user->id)
            ->where('operational_date', $today)
            ->where('is_planned', true)
            ->count();

        $visitDone = VisitPlan::where('salesman_id', $user->id)
            ->where('operational_date', $today)
            ->whereIn('status', ['COMPLETED', 'NO_ORDER', 'OUTLET_CLOSED'])
            ->count();

        $visitInProgress = VisitPlan::where('salesman_id', $user->id)
            ->where('operational_date', $today)
            ->where('status', 'IN_PROGRESS')
            ->count();

        // Pending visits (belum dikunjungi)
        $pendingVisits = VisitPlan::where('salesman_id', $user->id)
            ->where('operational_date', $today)
            ->whereIn('status', ['PLANNED', 'IN_PROGRESS'])
            ->with(['customer.area'])
            ->orderByRaw("CASE status WHEN 'IN_PROGRESS' THEN 1 ELSE 2 END")
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'customer_name' => $v->customer->customer_name ?? '-',
                'area_name' => $v->customer->area->area_name ?? '-',
                'status' => $v->status,
                'has_outstanding' => Invoice::where('customer_id', $v->customer_id)
                    ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
                    ->exists(),
            ])
            ->values();

        // Transaksi hari ini
        $todayOrderCount = SalesOrder::where('salesman_id', $user->id)
            ->where('operational_date', $today)
            ->where('status', 'POSTED')
            ->count();

        return view('livewire.pwa.dashboard', [
            'userName' => $user->name,
            'today' => $today->translatedFormat('l, d F Y'),
            'stockItems' => $stockItems,
            'totalStockQty' => (int) $totalStockQty,
            'visitTotal' => $visitTotal,
            'visitDone' => $visitDone,
            'visitInProgress' => $visitInProgress,
            'pendingVisits' => $pendingVisits,
            'todayOrderCount' => $todayOrderCount,
            'pendingSyncCount' => 0, // IndexedDB offline queue belum diimplementasi
        ])->layout('components.pwa.layout', ['title' => 'Dashboard']);
    }
}
