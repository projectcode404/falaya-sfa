<?php

namespace App\Livewire\Admin;

use App\Models\Area;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterType = '';

    public bool $showForm = false;

    public bool $isEdit = false;

    public ?int $editingId = null;

    public string $customer_code = '';

    public string $customer_name = '';

    public string $address = '';

    public string $area_id = '';

    public string $customer_type = 'CASH';

    public string $latitude = '';

    public string $longitude = '';

    public string $radius_tolerance_meter = '';

    public string $credit_limit = '';

    public string $credit_term_days = '';

    public string $owner_name = '';

    public string $owner_phone = '';

    protected function rules(): array
    {
        $uniqueCode = $this->isEdit
            ? 'unique:customers,customer_code,'.$this->editingId.',id,deleted_at,NULL'
            : 'unique:customers,customer_code,NULL,id,deleted_at,NULL';

        return [
            'customer_code' => ['required', 'string', 'max:30', $uniqueCode],
            'customer_name' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'customer_type' => ['required', 'in:CASH,CREDIT'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_tolerance_meter' => ['nullable', 'integer', 'min:10'],
            'credit_limit' => ['required_if:customer_type,CREDIT', 'nullable', 'numeric', 'min:0'],
            'credit_term_days' => ['required_if:customer_type,CREDIT', 'nullable', 'integer', 'in:7,14,30'],
            'owner_name' => ['nullable', 'string', 'max:100'],
            'owner_phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->showForm = true;
        $this->dispatch('formOpened');
        $this->js('window.dispatchEvent(new CustomEvent("formOpened"))');
    }

    public function openEdit(int $id): void
    {
        $c = Customer::findOrFail($id);
        $this->editingId = $c->id;
        $this->customer_code = $c->customer_code;
        $this->customer_name = $c->customer_name;
        $this->address = $c->address;
        $this->area_id = (string) $c->area_id;
        $this->customer_type = $c->customer_type;
        $this->latitude = (string) ($c->latitude ?? '');
        $this->longitude = (string) ($c->longitude ?? '');
        $this->radius_tolerance_meter = (string) ($c->radius_tolerance_meter ?? '');
        $this->credit_limit = (string) ($c->credit_limit ?? '');
        $this->credit_term_days = (string) ($c->credit_term_days ?? '');
        $this->owner_name = $c->owner_name ?? '';
        $this->owner_phone = $c->owner_phone ?? '';
        $this->isEdit = true;
        $this->showForm = true;
        $this->dispatch('formOpened');
        $this->js('window.dispatchEvent(new CustomEvent("formOpened"))');
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'customer_code' => $this->customer_code,
            'customer_name' => $this->customer_name,
            'address' => $this->address,
            'area_id' => $this->area_id,
            'customer_type' => $this->customer_type,
            'latitude' => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
            'radius_tolerance_meter' => $this->radius_tolerance_meter ?: null,
            'credit_limit' => $this->customer_type === 'CREDIT' ? $this->credit_limit : null,
            'credit_term_days' => $this->customer_type === 'CREDIT' ? $this->credit_term_days : null,
            'owner_name' => $this->owner_name ?: null,
            'owner_phone' => $this->owner_phone ?: null,
            'updated_at' => now(),
        ];

        if ($this->isEdit) {
            Customer::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Customer berhasil diperbarui.');
        } else {
            Customer::create(array_merge($data, [
                'status' => $this->customer_type === 'CASH' ? 'ACTIVE' : 'PENDING_APPROVAL',
                'requested_by' => Auth::id(),
                'created_at' => now(),
            ]));
            session()->flash('success', 'Customer berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'customer_code', 'customer_name', 'address',
            'area_id', 'latitude', 'longitude', 'radius_tolerance_meter',
            'credit_limit', 'credit_term_days', 'owner_name', 'owner_phone',
        ]);
        $this->customer_type = 'CASH';
        $this->resetValidation();
    }

    public function render()
    {
        $customers = Customer::withTrashed()
            ->with('area')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('customer_name', 'ilike', '%'.$this->search.'%')
                ->orWhere('customer_code', 'ilike', '%'.$this->search.'%')
            ))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn ($q) => $q->where('customer_type', $this->filterType))
            ->orderBy('customer_name')
            ->paginate(15);

        $areas = Area::where('is_active', true)->orderBy('area_name')->get();

        return view('livewire.admin.customer-manager', compact('customers', 'areas'))
            ->layout('components.layouts.app', ['title' => 'Manajemen Customer']);
    }
}
