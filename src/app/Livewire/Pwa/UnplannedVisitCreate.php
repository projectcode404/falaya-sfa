<?php

namespace App\Livewire\Pwa;

use App\Actions\Visit\CreateUnplannedVisitAction;
use App\DomainServices\OperationalDateService;
use App\Models\Customer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UnplannedVisitCreate extends Component
{
    public string $search = '';

    public ?int $selectedCustomerId = null;

    public string $submitError = '';

    public function selectCustomer(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;
        $this->submitError = '';
    }

    public function clearSelection(): void
    {
        $this->selectedCustomerId = null;
    }

    public function confirm(CreateUnplannedVisitAction $action, OperationalDateService $dateService): void
    {
        $this->submitError = '';

        if (! $this->selectedCustomerId) {
            $this->submitError = 'Pilih outlet terlebih dahulu.';

            return;
        }

        $operationalDate = $dateService->current()->toDateString();

        try {
            $visitPlan = $action->execute(
                Auth::id(),
                $this->selectedCustomerId,
                $operationalDate
            );
        } catch (UniqueConstraintViolationException $e) {
            $this->submitError = 'Kunjungan ke outlet ini sudah ada untuk hari ini.';

            return;
        } catch (\LogicException $e) {
            $this->submitError = $e->getMessage();

            return;
        }

        $this->redirect(route('pwa.pages.visits.detail', $visitPlan->id), navigate: false);
    }

    public function render()
    {
        $selectedCustomer = $this->selectedCustomerId
            ? Customer::find($this->selectedCustomerId)
            : null;

        $customers = Customer::query()
            ->where('status', 'ACTIVE')
            ->when($this->search, fn ($q) => $q->where('customer_name', 'ilike', "%{$this->search}%"))
            ->with('area')
            ->orderBy('customer_name')
            ->limit(30)
            ->get();

        return view('livewire.pwa.unplanned-visit-create', [
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
            'submitError' => $this->submitError,
        ])->layout('components.pwa.layout', ['title' => 'Kunjungan Tidak Terjadwal']);
    }
}
