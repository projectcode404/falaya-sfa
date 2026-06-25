<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManager extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public bool $isEdit = false;

    public ?int $editingId = null;

    public string $product_code = '';

    public string $product_name = '';

    public string $variant = '';

    public string $category = '';

    public string $unit = '';

    public string $selling_price = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        $uniqueCode = $this->isEdit
            ? 'unique:products,product_code,'.$this->editingId.',id,deleted_at,NULL'
            : 'unique:products,product_code,NULL,id,deleted_at,NULL';

        return [
            'product_code' => ['required', 'string', 'max:30', $uniqueCode],
            'product_name' => ['required', 'string', 'max:100'],
            'variant' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:20'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingId = $product->id;
        $this->product_code = $product->product_code;
        $this->product_name = $product->product_name;
        $this->variant = $product->variant ?? '';
        $this->category = $product->category ?? '';
        $this->unit = $product->unit;
        $this->selling_price = (string) $product->selling_price;
        $this->is_active = $product->is_active;
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'product_code' => $this->product_code,
            'product_name' => $this->product_name,
            'variant' => $this->variant ?: null,
            'category' => $this->category ?: null,
            'unit' => $this->unit,
            'selling_price' => $this->selling_price,
            'is_active' => $this->is_active,
        ];

        if ($this->isEdit) {
            $product = Product::findOrFail($this->editingId);
            $product->update(array_merge($data, ['updated_by' => Auth::id()]));
            session()->flash('success', 'Produk berhasil diperbarui.');
        } else {
            Product::create(array_merge($data, [
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]));
            session()->flash('success', 'Produk berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update([
            'is_active' => ! $product->is_active,
            'updated_by' => Auth::id(),
        ]);
        session()->flash('success', 'Status produk diperbarui.');
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'product_code', 'product_name',
            'variant', 'category', 'unit', 'selling_price',
        ]);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $products = Product::withTrashed()
            ->where(function ($q) {
                $q->where('product_name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('product_code', 'ilike', '%'.$this->search.'%');
            })
            ->orderBy('product_name')
            ->paginate(15);

        return view('livewire.admin.product-manager', compact('products'))
            ->layout('components.layouts.app', ['title' => 'Manajemen Produk']);
    }
}
