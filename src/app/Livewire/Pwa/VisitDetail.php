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

    public string $actionError = '';

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
        } catch (\LogicException $e) {
            $this->visitPlan->refresh()->load(['customer.area', 'realization']);
        }
    }

    public function checkout(CheckoutVisitAction $action): void
    {
        $this->actionError = '';

        try {
            $action->execute($this->visitPlan, []);
            $this->visitPlan->refresh();
        } catch (\LogicException $e) {
            $this->actionError = $e->getMessage();
        }
    }

    public function markOutletClosed(ManualUpdateVisitStatusAction $action): void
    {
        $this->actionError = '';

        // Outlet Tutup hanya relevan untuk visit yang BELUM check-in sama
        // sekali (outlet tidak buka, tidak ada yang menerima kedatangan).
        // Bila sudah IN_PROGRESS, jalur yang benar adalah Checkout --
        // bukan Outlet Tutup, supaya VisitRealization (checkin/checkout)
        // tetap tercatat konsisten.
        if ($this->visitPlan->status !== 'PLANNED') {
            $this->actionError = 'Kunjungan sudah dimulai. Gunakan Check-out untuk menyelesaikan.';

            return;
        }

        try {
            $action->execute($this->visitPlan, 'OUTLET_CLOSED');
            $this->visitPlan->refresh();
        } catch (\LogicException $e) {
            $this->actionError = $e->getMessage();
        }
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

        $hasPostedOrder = $this->visitPlan->salesOrders()
            ->where('status', 'POSTED')
            ->exists();

        // Riwayat SO & Payment untuk visit ini -- ditampilkan agar salesman
        // bisa cek dulu sebelum checkout (apa yang sudah tercatat).
        $visitOrders = $this->visitPlan->salesOrders()
            ->whereIn('status', ['DRAFT', 'POSTED'])
            ->get(['id', 'document_number', 'status', 'payment_type', 'total_amount']);

        $visitPayments = $this->visitPlan->payments()
            ->whereIn('status', ['DRAFT', 'POSTED'])
            ->get(['id', 'payment_number', 'status', 'payment_method', 'total_amount']);

        return view('livewire.pwa.visit-detail', [
            'visit' => $this->visitPlan,
            'customerOutstanding' => $customerOutstanding,
            'outstandingTotal' => $outstandingTotal,
            'outstandingInvoices' => $outstandingInvoices,
            'defaultRadius' => $defaultRadius,
            'isCheckedIn' => $isCheckedIn,
            'isDone' => $isDone,
            'hasPostedOrder' => $hasPostedOrder,
            'actionError' => $this->actionError,
            'visitOrders' => $visitOrders,
            'visitPayments' => $visitPayments,
        ])->layout('components.pwa.layout', ['title' => 'Detail Kunjungan']);
    }
}
