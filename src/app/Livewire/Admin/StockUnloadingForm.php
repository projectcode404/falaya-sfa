<?php

namespace App\Livewire\Admin;

use App\Actions\Inventory\CreateStockUnloadingAction;
use App\Actions\Inventory\PostStockUnloadingAction;
use App\DomainServices\OperationalDateService;
use App\Models\StockBalance;
use App\Models\StockUnloading;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class StockUnloadingForm extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $salesman_id = '';

    public array $items = [];

    public string $filterSalesman = '';

    protected function rules(): array
    {
        return [
            'salesman_id' => ['required', 'integer', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function updatingFilterSalesman(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['salesman_id', 'items']);
        $this->showForm = true;
    }

    public function updatedSalesmanId(): void
    {
        $this->loadSalesmanStock();
    }

    private function loadSalesmanStock(): void
    {
        if (! $this->salesman_id) {
            $this->items = [];

            return;
        }

        $this->items = StockBalance::with('product')
            ->where('holder_type', 'SALESMAN')
            ->where('holder_id', $this->salesman_id)
            ->where('condition', 'GOOD')
            ->where('qty', '>', 0)
            ->get()
            ->map(fn ($b) => [
                'product_id' => $b->product_id,
                'product_name' => $b->product->product_name,
                'unit' => $b->product->unit,
                'max_qty' => (float) $b->qty,
                'qty' => (float) $b->qty,
            ])
            ->toArray();
    }

    public function save(
        CreateStockUnloadingAction $createAction,
        PostStockUnloadingAction $postAction
    ): void {
        $this->validate();

        $itemsToUnload = collect($this->items)
            ->filter(fn ($i) => $i['qty'] > 0)
            ->map(fn ($i) => ['product_id' => $i['product_id'], 'qty' => $i['qty']])
            ->values()
            ->toArray();

        if (empty($itemsToUnload)) {
            session()->flash('error', 'Tidak ada stok yang perlu di-unloading.');
            $this->showForm = false;

            return;
        }

        try {
            $unloading = $createAction->execute((int) $this->salesman_id, $itemsToUnload);
            $postAction->execute($unloading);
            session()->flash('success', 'Stock Unloading '.$unloading->document_number.' berhasil diposting.');
        } catch (\RuntimeException|\LogicException $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->showForm = false;
        $this->reset(['salesman_id', 'items']);
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->reset(['salesman_id', 'items']);
    }

    public function render(OperationalDateService $dateService)
    {
        $unloadings = StockUnloading::with(['salesman', 'items.product'])
            ->when($this->filterSalesman, fn ($q) => $q->where('salesman_id', $this->filterSalesman))
            ->orderByDesc('created_at')
            ->paginate(15);

        $salesmen = User::where('role', 'SALESMAN')->where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.stock-unloading-form', compact('unloadings', 'salesmen'))
            ->layout('components.layouts.app', ['title' => 'Stock Unloading']);
    }
}
