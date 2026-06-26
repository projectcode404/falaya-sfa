<?php

namespace App\Http\Controllers\Owner;

use App\Actions\Customer\ApproveCustomerCreditAction;
use App\Actions\Customer\RejectCustomerCreditAction;
use App\Actions\CustomerReturn\ApproveCustomerReturnAction;
use App\Actions\CustomerReturn\RejectCustomerReturnAction;
use App\Actions\Inventory\ApproveStockAdjustmentAction;
use App\Actions\Inventory\ApproveStockWriteoffAction;
use App\Actions\Inventory\RejectStockAdjustmentAction;
use App\Actions\Inventory\RejectStockWriteoffAction;
use App\Actions\Sales\ApproveCreditOverrideAction;
use App\DomainServices\OperationalDateService;
use App\Http\Controllers\Controller;
use App\Models\CreditOverrideRequest;
use App\Models\Customer;
use App\Models\CustomerReturn;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\StockAdjustment;
use App\Models\StockBalance;
use App\Models\StockWriteoff;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerController extends Controller
{
    public function dashboard(OperationalDateService $dateService): View
    {
        $today = $dateService->current();

        $penjualanHariIni = SalesOrder::where('status', 'POSTED')
            ->where('operational_date', $today)
            ->sum('total_amount');

        $outstandingPiutang = Invoice::whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('remaining_amount');

        $invoiceOverdue = Invoice::where('status', 'OVERDUE')->count();

        $approvalPending =
            Customer::where('status', 'PENDING_APPROVAL')->count() +
            StockAdjustment::where('status', 'PENDING_APPROVAL')->count() +
            CustomerReturn::where('status', 'PENDING_APPROVAL')->count() +
            StockWriteoff::where('status', 'PENDING_APPROVAL')->count() +
            CreditOverrideRequest::where('status', 'PENDING')->count();

        $salesmen = User::role('SALESMAN')
            ->where('is_active', true)
            ->get()
            ->map(function ($s) use ($today) {
                $totalVisit = VisitPlan::where('salesman_id', $s->id)
                    ->where('operational_date', $today)
                    ->where('is_planned', true)
                    ->count();
                $visitDone = VisitPlan::where('salesman_id', $s->id)
                    ->where('operational_date', $today)
                    ->whereIn('status', ['COMPLETED', 'NO_ORDER', 'OUTLET_CLOSED'])
                    ->count();
                $omzet = SalesOrder::where('salesman_id', $s->id)
                    ->where('operational_date', $today)
                    ->where('status', 'POSTED')
                    ->sum('total_amount');
                $compliance = $totalVisit > 0
                    ? (int) round($visitDone / $totalVisit * 100)
                    : 0;

                return [
                    'name' => $s->name,
                    'compliance' => $compliance,
                    'omzet' => $omzet,
                ];
            });

        $gudangGood = StockBalance::where('holder_type', 'WAREHOUSE')
            ->where('condition', 'GOOD')
            ->sum('qty');

        $gudangBad = StockBalance::where('holder_type', 'WAREHOUSE')
            ->where('condition', 'BAD')
            ->sum('qty');

        $approvalTerbaru = collect([
            Customer::where('status', 'PENDING_APPROVAL')
                ->with('requestedBy')->latest()->take(3)->get()
                ->map(fn ($c) => [
                    'type' => 'Customer Baru',
                    'label' => $c->customer_name,
                    'by' => $c->requestedBy->name ?? '-',
                    'url' => '/owner/approvals?tab=customer',
                ]),
            StockAdjustment::where('status', 'PENDING_APPROVAL')
                ->with('createdBy')->latest()->take(3)->get()
                ->map(fn ($a) => [
                    'type' => 'Stock Adjustment',
                    'label' => $a->document_number,
                    'by' => $a->createdBy->name ?? '-',
                    'url' => '/owner/approvals?tab=adjustment',
                ]),
            CreditOverrideRequest::where('status', 'PENDING')
                ->with('requestedBy', 'salesOrder')->latest('requested_at')->take(3)->get()
                ->map(fn ($o) => [
                    'type' => 'Credit Override',
                    'label' => $o->salesOrder->document_number ?? '-',
                    'by' => $o->requestedBy->name ?? '-',
                    'url' => '/owner/approvals?tab=override',
                ]),
        ])->flatten(1)->take(5);

        return view('owner.dashboard', compact(
            'penjualanHariIni', 'outstandingPiutang', 'invoiceOverdue',
            'approvalPending', 'salesmen', 'gudangGood', 'gudangBad', 'approvalTerbaru'
        ));
    }

    public function approvals(Request $request): View
    {
        $tab = $request->get('tab', 'customer');
        $customerCredits = Customer::where('status', 'PENDING_APPROVAL')->with('requestedBy')->latest()->get();
        $stockAdjustments = StockAdjustment::where('status', 'PENDING_APPROVAL')->with('createdBy', 'product')->latest()->get();
        $customerReturns = CustomerReturn::where('status', 'PENDING_APPROVAL')->with('createdBy', 'customer', 'invoice')->latest()->get();
        $stockWriteoffs = StockWriteoff::where('status', 'PENDING_APPROVAL')->with('createdBy', 'product')->latest()->get();
        $creditOverrides = CreditOverrideRequest::where('status', 'PENDING')->with('requestedBy', 'customer', 'salesOrder')->latest('requested_at')->get();

        return view('owner.approvals', compact(
            'tab', 'customerCredits', 'stockAdjustments',
            'customerReturns', 'stockWriteoffs', 'creditOverrides'
        ));
    }

    public function approveCustomerCredit(Request $request, Customer $customer, ApproveCustomerCreditAction $action): RedirectResponse
    {
        $data = $request->validate(['credit_limit' => ['nullable', 'numeric', 'min:0']]);
        $action->execute($customer, isset($data['credit_limit']) ? (float) $data['credit_limit'] : null);

        return back()->with('success', "Customer {$customer->customer_name} disetujui.");
    }

    public function rejectCustomerCredit(Request $request, Customer $customer, RejectCustomerCreditAction $action): RedirectResponse
    {
        $request->validate(['notes' => ['required', 'string']]);
        $action->execute($customer, $request->string('notes')->toString());

        return back()->with('success', "Customer {$customer->customer_name} ditolak.");
    }

    public function approveStockAdjustment(StockAdjustment $adjustment, ApproveStockAdjustmentAction $action): RedirectResponse
    {
        $action->execute($adjustment);

        return back()->with('success', "Stock Adjustment {$adjustment->document_number} disetujui.");
    }

    public function rejectStockAdjustment(Request $request, StockAdjustment $adjustment, RejectStockAdjustmentAction $action): RedirectResponse
    {
        $request->validate(['notes' => ['required', 'string']]);
        $action->execute($adjustment, $request->string('notes')->toString());

        return back()->with('success', "Stock Adjustment {$adjustment->document_number} ditolak.");
    }

    public function approveCustomerReturn(CustomerReturn $return, ApproveCustomerReturnAction $action): RedirectResponse
    {
        $action->execute($return);

        return back()->with('success', "Customer Return {$return->document_number} disetujui.");
    }

    public function rejectCustomerReturn(Request $request, CustomerReturn $return, RejectCustomerReturnAction $action): RedirectResponse
    {
        $request->validate(['notes' => ['required', 'string']]);
        $action->execute($return, $request->string('notes')->toString());

        return back()->with('success', "Customer Return {$return->document_number} ditolak.");
    }

    public function approveStockWriteoff(StockWriteoff $writeoff, ApproveStockWriteoffAction $action): RedirectResponse
    {
        $action->execute($writeoff);

        return back()->with('success', "Stock Write-off {$writeoff->document_number} disetujui.");
    }

    public function rejectStockWriteoff(Request $request, StockWriteoff $writeoff, RejectStockWriteoffAction $action): RedirectResponse
    {
        $request->validate(['notes' => ['required', 'string']]);
        $action->execute($writeoff, $request->string('notes')->toString());

        return back()->with('success', "Stock Write-off {$writeoff->document_number} ditolak.");
    }

    public function approveCreditOverride(CreditOverrideRequest $override, ApproveCreditOverrideAction $action): RedirectResponse
    {
        $action->execute($override);

        return back()->with('success', 'Credit Override disetujui.');
    }

    public function rejectCreditOverride(Request $request, CreditOverrideRequest $override): RedirectResponse
    {
        $request->validate(['notes' => ['required', 'string']]);
        $override->update([
            'status' => 'REJECTED',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'approval_notes' => $request->notes,
        ]);

        return back()->with('success', 'Credit Override ditolak.');
    }
}
