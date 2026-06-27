<?php

namespace App\Livewire\Pwa;

use App\Actions\Visit\CheckinVisitAction;
use App\Actions\Visit\CheckoutVisitAction;
use App\Actions\Visit\ManualUpdateVisitStatusAction;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\VisitPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class VisitDetail extends Component
{
    public VisitPlan $visitPlan;

    public function mount(VisitPlan $visitPlan): void
    {
        if ($visitPlan->salesman_id !== Auth::id()) {
            abort(403);
        }

        $this->visitPlan = $visitPlan->load(['customer.area', 'realization']);
    }

    public function checkin(array $gpsInfo, CheckinVisitAction $action): void
    {
        $gpsData = [
            'unavailable' => $gpsInfo['unavailable'] ?? false,
            'latitude' => $gpsInfo['lat'] ?? null,
            'longitude' => $gpsInfo['lng'] ?? null,
            'accuracy' => $gpsInfo['acc'] ?? null,
        ];

        $idempotencyKey = Str::uuid()->toString();

        try {
            $action->execute($this->visitPlan, $gpsData, $idempotencyKey);
            $this->visitPlan->refresh()->load(['customer.area', 'realization']);
        } catch (\RuntimeException $e) {
            $this->dispatch('checkin-error', message: $e->getMessage());
        }
    }

    public function checkout(CheckoutVisitAction $action): void
    {
        try {
            $action->execute($this->visitPlan, []);
            $this->visitPlan->refresh();
        } catch (\LogicException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function markOutletClosed(ManualUpdateVisitStatusAction $action): void
    {
        $action->execute($this->visitPlan, 'OUTLET_CLOSED');
        $this->visitPlan->refresh();
    }

    public function markNoOrder(ManualUpdateVisitStatusAction $action): void
    {
        $action->execute($this->visitPlan, 'NO_ORDER');
        $this->visitPlan->refresh();
    }

    public function render()
    {
        $customer = $this->visitPlan->customer;

        // Outstanding invoices
        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->orderBy('due_date')
            ->get();

        $outstandingTotal = $invoices->sum('remaining_amount');

        $outstandingInvoices = $invoices->map(fn ($inv) => [
            'invoice_number' => $inv->invoice_number,
            'remaining_amount' => $inv->remaining_amount,
            'due_date' => $inv->due_date,
            'is_overdue' => $inv->status === 'OVERDUE',
            'days_overdue' => $inv->status === 'OVERDUE'
                ? now()->diffInDays($inv->due_date)
                : 0,
            'due_soon' => $inv->status !== 'OVERDUE' &&
                now()->diffInDays($inv->due_date, false) <= 3 &&
                now()->diffInDays($inv->due_date, false) >= 0,
            'days_to_due' => max(0, now()->diffInDays($inv->due_date, false)),
        ])->values();

        $customerOutstanding = $invoices->sum('remaining_amount');

        $defaultRadius = (int) (Setting::where('setting_key', 'default_radius_tolerance_meter')
            ->value('setting_value') ?? 100);

        $isCheckedIn = in_array($this->visitPlan->status, ['IN_PROGRESS', 'COMPLETED', 'NO_ORDER', 'OUTLET_CLOSED']);
        $isDone = in_array($this->visitPlan->status, ['COMPLETED', 'NO_ORDER', 'OUTLET_CLOSED', 'SKIPPED']);

        return view('livewire.pwa.visit-detail', [
            'visit' => $this->visitPlan,
            'customerOutstanding' => $customerOutstanding,
            'outstandingTotal' => $outstandingTotal,
            'outstandingInvoices' => $outstandingInvoices,
            'defaultRadius' => $defaultRadius,
            'isCheckedIn' => $isCheckedIn,
            'isDone' => $isDone,
        ])->layout('components.pwa.layout', ['title' => 'Detail Kunjungan']);
    }
}
