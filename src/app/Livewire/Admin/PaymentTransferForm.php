<?php

namespace App\Livewire\Admin;

use App\Actions\Collection\CreatePaymentAction;
use App\Actions\Collection\PostPaymentAction;
use App\Models\Customer;
use App\Models\Invoice;
use Livewire\Component;

class PaymentTransferForm extends Component
{
    public string $search = '';

    public array $customers = [];

    public ?int $selectedCustomerId = null;

    public ?string $selectedCustomerName = null;

    public array $invoices = [];

    public array $selected = [];

    public array $amounts = [];

    public string $notes = '';

    public string $submitError = '';

    public string $submitSuccess = '';

    public function mount(): void
    {
        $this->loadCustomers();
    }

    public function updatedSearch(): void
    {
        $this->loadCustomers();
    }

    public function loadCustomers(): void
    {
        $this->customers = Customer::where('customer_type', 'CREDIT')
            ->where('status', 'ACTIVE')
            ->when($this->search, fn ($q) => $q->where('customer_name', 'like', '%'.$this->search.'%'))
            ->orderBy('customer_name')
            ->limit(20)
            ->get(['id', 'customer_name', 'customer_code'])
            ->toArray();
    }

    public function selectCustomer(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);

        $this->selectedCustomerId = $customer->id;
        $this->selectedCustomerName = $customer->customer_name;
        $this->submitError = '';
        $this->submitSuccess = '';

        $this->invoices = Invoice::where('customer_id', $customerId)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->orderBy('due_date')
            ->get()
            ->map(fn ($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'remaining_amount' => (float) $inv->remaining_amount,
                'due_date' => is_string($inv->due_date) ? $inv->due_date : $inv->due_date->format('d/m/Y'),
                'status' => $inv->status,
            ])
            ->toArray();

        $this->selected = [];
        $this->amounts = [];
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

    public function submitTransfer(CreatePaymentAction $createAction, PostPaymentAction $postAction): void
    {
        $this->submitError = '';
        $this->submitSuccess = '';

        if (! $this->selectedCustomerId) {
            $this->submitError = 'Pilih customer terlebih dahulu.';

            return;
        }

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
                $this->selectedCustomerId,
                $total,
                'TRANSFER',
                $allocations,
                null,
                $this->notes ?: null,
            );

            $payment = $postAction->execute($payment);

            $this->submitSuccess = "Payment {$payment->payment_number} berhasil dicatat! Receipt: {$payment->receipt->receipt_number}";

            $this->selectedCustomerId = null;
            $this->selectedCustomerName = null;
            $this->invoices = [];
            $this->notes = '';

        } catch (\RuntimeException $e) {
            $this->submitError = $e->getMessage();
        } catch (\LogicException $e) {
            $this->submitError = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.admin.payment-transfer-form')->layout('components.layouts.app', ['title' => 'Payment Transfer']);
    }
}
