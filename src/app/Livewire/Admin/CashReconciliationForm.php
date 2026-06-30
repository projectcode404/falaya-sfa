<?php

namespace App\Livewire\Admin;

use App\Actions\Collection\ProcessCashReconciliationAction;
use App\DomainServices\OperationalDateService;
use App\Models\DailyCashReconciliation;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Models\User;
use Livewire\Component;

class CashReconciliationForm extends Component
{
    public string $operationalDate = '';

    public array $salesmanList = [];

    public ?int $selectedSalesmanId = null;

    public float $cashSalesTotal = 0;

    public float $collectionCashTotal = 0;

    public float $systemTotal = 0;

    public string $actualReceived = '';

    public string $notes = '';

    public ?array $lastResult = null;

    public string $submitError = '';

    public function mount(OperationalDateService $dateService): void
    {
        $this->operationalDate = $dateService->current()->toDateString();
        $this->loadSalesmanList();
    }

    public function loadSalesmanList(): void
    {
        $this->salesmanList = User::role('SALESMAN')
            ->where('is_active', true)
            ->get()
            ->map(function ($s) {
                $existing = DailyCashReconciliation::where('salesman_id', $s->id)
                    ->where('operational_date', $this->operationalDate)
                    ->first();

                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'status' => $existing?->status ?? 'PENDING',
                ];
            })
            ->toArray();
    }

    public function selectSalesman(int $salesmanId): void
    {
        $this->selectedSalesmanId = $salesmanId;
        $this->actualReceived = '';
        $this->notes = '';
        $this->lastResult = null;
        $this->submitError = '';

        $this->cashSalesTotal = (float) SalesOrder::where('salesman_id', $salesmanId)
            ->where('operational_date', $this->operationalDate)
            ->where('payment_type', 'CASH')
            ->where('status', 'POSTED')
            ->sum('total_amount');

        $this->collectionCashTotal = (float) Payment::where('collected_by', $salesmanId)
            ->where('operational_date', $this->operationalDate)
            ->where('payment_method', 'CASH')
            ->where('status', 'POSTED')
            ->sum('total_amount');

        $this->systemTotal = $this->cashSalesTotal + $this->collectionCashTotal;
    }

    public function submitReconciliation(ProcessCashReconciliationAction $action): void
    {
        $this->submitError = '';

        if (! $this->selectedSalesmanId) {
            $this->submitError = 'Pilih salesman terlebih dahulu.';

            return;
        }

        if ($this->actualReceived === '' || ! is_numeric($this->actualReceived)) {
            $this->submitError = 'Masukkan jumlah uang yang diterima.';

            return;
        }

        try {
            $reconciliation = $action->execute(
                $this->selectedSalesmanId,
                (float) $this->actualReceived,
                $this->notes ?: null,
            );

            $this->lastResult = [
                'status' => $reconciliation->status,
                'difference' => (float) $reconciliation->difference,
                'system_total' => (float) $reconciliation->system_total,
                'actual_received' => (float) $reconciliation->actual_received,
            ];

            $this->loadSalesmanList();
            $this->selectedSalesmanId = null;

        } catch (\Exception $e) {
            $this->submitError = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.admin.cash-reconciliation-form')->layout('components.layouts.app', ['title' => 'Cash Reconciliation']);
    }
}
