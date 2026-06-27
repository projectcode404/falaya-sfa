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

    public bool $showForm = false;

    public string $salesman_id = '';

    public array $items = [];

    // Product search
    public string $productSearch = '';

    public array $searchResults = [];

    public bool $showSearchResults = false;

    // List filter
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
        $this->reset(['salesman_id', 'items', 'productSearch', 'searchResults']);
        $this->showSearchResults = false;
        $this->showForm = true;
    }

    public function updatedProductSearch(): void
    {
        $search = trim($this->productSearch);

        if (strlen($search) < 2) {
            $this->searchResults = [];
            $this->showSearchResults = false;

            return;
        }

        $existingIds = collect($this->items)->pluck('product_id')->toArray();

        $this->searchResults = Product::where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('product_name', 'ilike', "%{$search}%")
                    ->orWhere('variant', 'ilike', "%{$search}%")
                    ->orWhere('product_code', 'ilike', "%{$search}%");
            })
            ->whereNotIn('id', $existingIds)
            ->orderBy('product_name')
            ->orderBy('variant')
            ->limit(8)
            ->get()
            ->map(function ($p) {
                $gudangQty = (float) (StockBalance::where('product_id', $p->id)
                    ->where('holder_type', 'WAREHOUSE')
                    ->whereNull('holder_id')
                    ->where('condition', 'GOOD')
                    ->value('qty') ?? 0);

                return [
                    'product_id' => $p->id,
                    'product_name' => $p->product_name,
                    'variant' => $p->variant ?? '',
                    'unit' => $p->unit,
                    'gudang_qty' => $gudangQty,
                ];
            })
            ->toArray();

        $this->showSearchResults = count($this->searchResults) > 0;
    }

    public function addProduct(int $productId): void
    {
        // Cegah duplikat
        foreach ($this->items as $item) {
            if ((int) $item['product_id'] === $productId) {
                $this->productSearch = '';
                $this->searchResults = [];
                $this->showSearchResults = false;

                return;
            }
        }

        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $gudangQty = (float) (StockBalance::where('product_id', $productId)
            ->where('holder_type', 'WAREHOUSE')
            ->whereNull('holder_id')
            ->where('condition', 'GOOD')
            ->value('qty') ?? 0);

        $this->items[] = [
            'product_id' => $product->id,
            'product_name' => $product->product_name,
            'variant' => $product->variant ?? '',
            'unit' => $product->unit,
            'gudang_qty' => $gudangQty,
            'qty' => 0,
        ];

        $this->productSearch = '';
        $this->searchResults = [];
        $this->showSearchResults = false;
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
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
        $this->reset(['salesman_id', 'items', 'productSearch', 'searchResults']);
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->reset(['salesman_id', 'items', 'productSearch', 'searchResults']);
    }

    public function render(OperationalDateService $dateService)
    {
        $loadings = StockLoading::with(['salesman', 'items.product'])
            ->when($this->filterSalesman, fn ($q) => $q->where('salesman_id', $this->filterSalesman))
            ->orderByDesc('created_at')
            ->paginate(15);

        $salesmen = User::where('role', 'SALESMAN')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.stock-loading-form', compact('loadings', 'salesmen'))
            ->layout('components.layouts.app', ['title' => 'Stock Loading']);
    }
}
