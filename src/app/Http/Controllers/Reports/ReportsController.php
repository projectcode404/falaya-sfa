<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\CollectionTask;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\StockBalance;
use App\Models\StockLedger;
use App\Models\User;
use App\Models\VisitPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    private function parseDateRange(Request $request): array
    {
        $from = $request->get('from')
            ? Carbon::parse($request->get('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->get('to')
            ? Carbon::parse($request->get('to'))->endOfDay()
            : now()->endOfDay();

        // Max 3 bulan
        if ($from->diffInMonths($to) > 3) {
            $to = $from->copy()->addMonths(3)->endOfDay();
        }

        return [$from, $to];
    }

    public function sales(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $rows = SalesOrder::with(['customer', 'salesman', 'items.product'])
            ->where('status', 'POSTED')
            ->whereBetween('operational_date', [$from->toDateString(), $to->toDateString()])
            ->when($request->salesman_id, fn ($q) => $q->where('salesman_id', $request->salesman_id))
            ->latest('operational_date')
            ->paginate(50);

        $salesmen = User::role('SALESMAN')->where('is_active', true)->get();

        return view('reports.sales', compact('rows', 'salesmen', 'from', 'to'));
    }

    public function stock(Request $request)
    {
        $balances = StockBalance::with('product')
            ->when($request->holder_type, fn ($q) => $q->where('holder_type', $request->holder_type))
            ->when($request->condition, fn ($q) => $q->where('condition', $request->condition))
            ->get()
            ->groupBy('product_id');

        return view('reports.stock', compact('balances'));
    }

    public function visits(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $salesmen = User::role('SALESMAN')->where('is_active', true)->get();

        $rows = VisitPlan::with(['salesman', 'customer'])
            ->whereBetween('operational_date', [$from->toDateString(), $to->toDateString()])
            ->where('is_planned', true)
            ->when($request->salesman_id, fn ($q) => $q->where('salesman_id', $request->salesman_id))
            ->latest('operational_date')
            ->paginate(50);

        return view('reports.visits', compact('rows', 'salesmen', 'from', 'to'));
    }

    public function ar(Request $request)
    {
        $rows = Invoice::with(['customer', 'salesman'])
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('due_date')
            ->paginate(50);

        return view('reports.ar', compact('rows'));
    }

    public function collectionRisk(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $rows = CollectionTask::with(['customer', 'salesman'])
            ->whereIn('status', ['NO_PAYMENT', 'RESCHEDULED'])
            ->whereBetween('operational_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('customer_id, salesman_id, COUNT(*) as total_skip, MAX(operational_date) as last_skip')
            ->groupBy('customer_id', 'salesman_id')
            ->orderByDesc('total_skip')
            ->with(['customer', 'salesman'])
            ->paginate(50);

        return view('reports.collection-risk', compact('rows', 'from', 'to'));
    }

    public function badStock(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $ledgers = StockLedger::with('product')
            ->where('condition', 'BAD')
            ->whereIn('source_type', ['ADJUSTMENT', 'CUSTOMER_RETURN'])
            ->whereBetween('operational_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $summary = $ledgers->groupBy('product_id')->map(function ($items) {
            $product = $items->first()->product;

            return [
                'product' => $product->product_name ?? '-',
                'adjustment' => $items->where('source_type', 'ADJUSTMENT')->sum('qty'),
                'return' => $items->where('source_type', 'CUSTOMER_RETURN')->sum('qty'),
                'total' => $items->sum('qty'),
                'nilai' => $items->sum(fn ($l) => abs($l->qty) * ($product->selling_price ?? 0)),
            ];
        });

        $currentBad = StockBalance::with('product')
            ->where('holder_type', 'WAREHOUSE')
            ->where('condition', 'BAD')
            ->where('qty', '>', 0)
            ->get();

        return view('reports.bad-stock', compact('summary', 'currentBad', 'from', 'to'));
    }
}
