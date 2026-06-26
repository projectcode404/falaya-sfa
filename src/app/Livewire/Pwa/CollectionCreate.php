<?php

namespace App\Livewire\Pwa;

use App\Models\Invoice;
use App\Models\VisitPlan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CollectionCreate extends Component
{
    public VisitPlan $visitPlan;

    public array $invoices = [];

    public array $selected = [];

    public array $amounts = [];

    public string $notes = '';

    public function mount(VisitPlan $visitPlan): void
    {
        if ($visitPlan->salesman_id !== Auth::id()) {
            abort(403);
        }

        $this->visitPlan = $visitPlan->load('customer');

        $this->invoices = Invoice::where('customer_id', $visitPlan->customer_id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->orderBy('due_date')
            ->get()
            ->map(fn ($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'total_amount' => (float) $inv->total_amount,
                'remaining_amount' => (float) $inv->remaining_amount,
                'due_date' => is_string($inv->due_date) ? $inv->due_date : $inv->due_date->format('d/m/Y'),
                'status' => $inv->status,
            ])
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

    public function render()
    {
        return view('livewire.pwa.collection-create', [
            'total' => $this->getTotal(),
        ])->layout('components.pwa.layout', ['title' => 'Catat Pembayaran']);
    }
}
