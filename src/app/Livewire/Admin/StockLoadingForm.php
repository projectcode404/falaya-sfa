<?php

namespace App\Livewire\Admin;

use App\Actions\Inventory\CreateStockLoadingAction;
use App\Actions\Inventory\PostStockLoadingAction;
use App\DomainServices\OperationalDateService;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockLoading;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class StockLoadingForm extends Component
{
    use WithPagination;

    // Form buat loading baru
    public bool $showForm = false;

    public string $salesman_id = '';

    public array $items = [];

    // List
    public string $filterSalesman = '';

    protected function rules(): array
    {
        return [
            'salesman_id' => ['required', 'integer', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
        ];
    }

    public function updatingFilterSalesman(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['salesman_id', 'items']);
        $this->initItems();
        $this->showForm = true;
    }

    public function updatedSalesmanId(): void
    {
        $this->initItems();
    }

    private function initItems(): void
    {
        $this->items = Product::where('is_active', true)
            ->orderBy('product_name')
            ->get()
            ->map(fn ($p) => [
                'product_id' => $p->id,
                'product_name' => $p->product_name,
                'unit' => $p->unit,
                'gudang_qty' => (float) StockBalance::where('product_id', $p->id)
                    ->where('holder_type', 'WAREHOUSE')
                    ->whereNull('holder_id')
                    ->where('condition', 'GOOD')
                    ->value('qty') ?? 0,
                'qty' => 0,
            ])
            ->toArray();
    }

    public function save(
        CreateStockLoadingAction $createAction,
        PostStockLoadingAction $postAction
    ): void {
        $this->validate();

        $itemsToLoad = collect($this->items)
            ->filter(fn ($i) => $i['qty'] > 0)
            ->map(fn ($i) => ['product_id' => $i['product_id'], 'qty' => $i['qty']])
            ->values()
            ->toArray();

        if (empty($itemsToLoad)) {
            $this->addError('items', 'Masukkan qty minimal 1 produk.');

            return;
        }

        try {
            $loading = $createAction->execute((int) $this->salesman_id, $itemsToLoad);
            $postAction->execute($loading);
            session()->flash('success', 'Stock Loading '.$loading->document_number.' berhasil diposting.');
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
        $loadings = StockLoading::with(['salesman', 'items.product'])
            ->when($this->filterSalesman, fn ($q) => $q->where('salesman_id', $this->filterSalesman))
            ->orderByDesc('created_at')
            ->paginate(15);

        $salesmen = User::where('role', 'SALESMAN')->where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.stock-loading-form', compact('loadings', 'salesmen'))
            ->layout('components.layouts.app', ['title' => 'Stock Loading']);
    }
}
