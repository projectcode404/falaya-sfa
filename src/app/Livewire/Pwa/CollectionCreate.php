<?php

namespace App\Livewire\Pwa;

use App\Actions\Collection\CreatePaymentAction;
use App\Actions\Collection\PostPaymentAction;
use App\Models\CollectionTask;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\VisitPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CollectionCreate extends Component
{
    public VisitPlan $visitPlan;

    public array $invoices = [];

    public array $selected = [];

    public array $amounts = [];

    public string $notes = '';

    public string $skipReason = '';

    public string $skipNote = '';

    public string $submitError = '';

    public string $submitSuccess = '';

    public function mount(VisitPlan $visitPlan): void
    {
        if ($visitPlan->salesman_id !== Auth::id()) {
            abort(403);
        }

        $this->visitPlan = $visitPlan->load('customer');

        $dueSoonDays = (int) Setting::get('collection_due_soon_days', 3);
        $today = now()->startOfDay();

        $this->invoices = Invoice::where('customer_id', $visitPlan->customer_id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->orderBy('due_date')
            ->get()
            ->map(function ($inv) use ($today, $dueSoonDays) {
                $dueDate = is_string($inv->due_date) ? Carbon::parse($inv->due_date) : $inv->due_date;
                $diffDays = $today->diffInDays($dueDate, false); // negatif = sudah lewat

                return [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'invoice_date' => is_string($inv->invoice_date) ? $inv->invoice_date : $inv->invoice_date->format('Y-m-d'),
                    'credit_term_days_snapshot' => $inv->credit_term_days_snapshot,
                    'total_amount' => (float) $inv->total_amount,
                    'paid_amount' => (float) $inv->paid_amount,
                    'remaining_amount' => (float) $inv->remaining_amount,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'status' => $inv->status,
                    'is_overdue' => $diffDays < 0,
                    'days_overdue' => $diffDays < 0 ? abs($diffDays) : 0,
                    'is_due_soon' => $diffDays >= 0 && $diffDays <= $dueSoonDays,
                    'days_to_due' => $diffDays >= 0 ? $diffDays : 0,
                ];
            })
            ->toArray();

        foreach ($this->invoices as $inv) {
            $this->selected[$inv['id']] = false;
            $this->amounts[$inv['id']] = $inv['remaining_amount'];
        }
    }

    public function toggleSelect(int $invoiceId): void
    {
        $this->selected[$invoiceId] = ! ($this->selected[$invoiceId] ?? false);
    }

    public function selectAll(): void
    {
        foreach ($this->invoices as $inv) {
            $this->selected[$inv['id']] = true;
        }
    }

    public function clearAll(): void
    {
        foreach ($this->invoices as $inv) {
            $this->selected[$inv['id']] = false;
        }
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->invoices as $inv) {
            if ($this->selected[$inv['id']] ?? false) {
                $total += (float) ($this->amounts[$inv['id']] ?? 0);
            }
        }

        return $total;
    }

    public function getSelectedCount(): int
    {
        return count(array_filter($this->selected));
    }

    public function submitPayment(CreatePaymentAction $createAction, PostPaymentAction $postAction): void
    {
        $this->submitError = '';
        $this->submitSuccess = '';

        $allocations = [];
        foreach ($this->invoices as $inv) {
            if ($this->selected[$inv['id']] ?? false) {
                $amount = (float) ($this->amounts[$inv['id']] ?? 0);
                if ($amount > 0) {
                    $allocations[$inv['id']] = $amount;
                }
            }
        }

        if (empty($allocations)) {
            $this->submitError = 'Pilih minimal 1 invoice.';

            return;
        }

        $total = array_sum($allocations);

        try {
            $payment = $createAction->execute(
                $this->visitPlan->customer_id,
                $total,
                'CASH',
                $allocations,
                $this->visitPlan->id,
                $this->notes ?: null,
            );
            $payment = $postAction->execute($payment);
            $this->submitSuccess = "Pembayaran {$payment->payment_number} berhasil dicatat!";
            $this->dispatch('payment-success');

            $this->redirect(route('pwa.pages.visits.detail', $this->visitPlan->id), navigate: false);
        } catch (\RuntimeException $e) {
            $this->submitError = $e->getMessage();
        } catch (\LogicException $e) {
            $this->submitError = $e->getMessage();
        }
    }

    public function submitSkip(): void
    {
        $this->submitError = '';
        $this->submitSuccess = '';

        if (! $this->skipReason) {
            $this->submitError = 'Pilih alasan terlebih dahulu.';

            return;
        }

        $taskId = $this->visitPlan->collectionTask?->id;

        if (! $taskId) {
            $this->redirect(route('pwa.pages.visits.detail', $this->visitPlan->id), navigate: false);

            return;
        }

        $task = CollectionTask::find($taskId);

        if (! $task || $task->salesman_id !== Auth::id()) {
            $this->submitError = 'Collection Task tidak ditemukan.';

            return;
        }

        $status = $this->skipReason === 'reschedule' ? 'RESCHEDULED' : 'NO_PAYMENT';
        $noteText = $this->skipReason === 'other' ? $this->skipNote : $this->skipReason;

        $task->update([
            'status' => $status,
            'result_notes' => $noteText ?: $this->skipReason,
            'updated_at' => now(),
        ]);

        $this->submitSuccess = 'Penagihan dilewati.';
        $this->dispatch('payment-success');

        $this->redirect(route('pwa.pages.visits.detail', $this->visitPlan->id), navigate: false);
    }

    public function render()
    {
        return view('livewire.pwa.collection-create', [
            'total' => $this->getTotal(),
            'selectedCount' => $this->getSelectedCount(),
            'customer' => $this->visitPlan->customer,
        ])->layout('components.pwa.layout', ['title' => 'Catat Pembayaran']);
    }
}
