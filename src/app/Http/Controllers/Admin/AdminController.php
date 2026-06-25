<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Closing\ExecuteDailyClosingAction;
use App\Actions\Closing\ValidateClosingChecklistAction;
use App\DomainServices\OperationalDateService;
use App\Http\Controllers\Controller;
use App\Models\DailyCashReconciliation;
use App\Models\StockLoading;
use App\Models\StockUnloading;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(OperationalDateService $dateService)
    {
        $today = $dateService->current();
        $isSynced = $dateService->isSyncedWithCalendar();

        $salesmen = User::role('SALESMAN')
            ->where('is_active', true)
            ->get()
            ->map(function ($s) use ($today) {
                $loading = StockLoading::where('salesman_id', $s->id)
                    ->where('operational_date', $today)
                    ->where('status', 'POSTED')
                    ->exists();

                $totalVisit = VisitPlan::where('salesman_id', $s->id)
                    ->where('operational_date', $today)
                    ->where('is_planned', true)
                    ->count();

                $visitDone = VisitPlan::where('salesman_id', $s->id)
                    ->where('operational_date', $today)
                    ->whereIn('status', ['COMPLETED', 'NO_ORDER', 'OUTLET_CLOSED'])
                    ->count();

                $unloaded = StockUnloading::where('salesman_id', $s->id)
                    ->where('operational_date', $today)
                    ->where('status', 'POSTED')
                    ->exists();

                $recon = DailyCashReconciliation::where('salesman_id', $s->id)
                    ->where('operational_date', $today)
                    ->whereIn('status', ['RECONCILED'])
                    ->exists();

                return [
                    'name' => $s->name,
                    'loading' => $loading,
                    'visit' => "{$visitDone}/{$totalVisit}",
                    'visit_done' => $totalVisit > 0 && $visitDone === $totalVisit,
                    'unloading' => $unloaded,
                    'cash_recon' => $recon,
                ];
            });

        return view('admin.dashboard', compact('today', 'isSynced', 'salesmen'));
    }

    public function closing(ValidateClosingChecklistAction $checklist, OperationalDateService $dateService)
    {
        $today = $dateService->current();
        $result = $checklist->execute();

        return view('admin.closing', compact('today', 'result'));
    }

    public function executeClosing(Request $request, ExecuteDailyClosingAction $action, OperationalDateService $dateService)
    {
        $today = $dateService->current();

        try {
            $action->execute($request->input('notes'));

            return redirect()->route('admin.closing')
                ->with('success', "Hari operasional {$today->toDateString()} berhasil ditutup.");
        } catch (\Exception $e) {
            return redirect()->route('admin.closing')
                ->with('error', 'Gagal menutup hari: '.$e->getMessage());
        }
    }
}
