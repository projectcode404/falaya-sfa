<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\User;
use App\Models\VisitSchedule;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class VisitScheduleManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterSalesman = '';

    public bool $showForm = false;

    public bool $isEdit = false;

    public ?int $editingId = null;

    public string $salesman_id = '';

    public string $customer_id = '';

    public string $day_of_week = '';

    public string $effective_from = '';

    public string $effective_to = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'salesman_id' => ['required', 'integer', 'exists:users,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
            'is_active' => ['boolean'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSalesman(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->effective_from = now()->toDateString();
        $this->isEdit = false;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $vs = VisitSchedule::findOrFail($id);
        $this->editingId = $vs->id;
        $this->salesman_id = (string) $vs->salesman_id;
        $this->customer_id = (string) $vs->customer_id;
        $this->day_of_week = (string) $vs->day_of_week;
        $this->effective_from = is_string($vs->effective_from) ? $vs->effective_from : $vs->effective_from->toDateString();
        $this->effective_to = $vs->effective_to ? (is_string($vs->effective_to) ? $vs->effective_to : $vs->effective_to->toDateString()) : '';
        $this->is_active = $vs->is_active;
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'salesman_id' => $this->salesman_id,
            'customer_id' => $this->customer_id,
            'day_of_week' => $this->day_of_week,
            'effective_from' => $this->effective_from,
            'effective_to' => $this->effective_to ?: null,
            'is_active' => $this->is_active,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ];

        if ($this->isEdit) {
            VisitSchedule::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Jadwal kunjungan berhasil diperbarui.');
        } else {
            VisitSchedule::create(array_merge($data, [
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]));
            session()->flash('success', 'Jadwal kunjungan berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $vs = VisitSchedule::findOrFail($id);
        $vs->update(['is_active' => ! $vs->is_active, 'updated_by' => Auth::id(), 'updated_at' => now()]);
        session()->flash('success', 'Status jadwal diperbarui.');
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'salesman_id', 'customer_id', 'day_of_week', 'effective_from', 'effective_to']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $schedules = VisitSchedule::with(['salesman', 'customer'])
            ->when($this->search, fn ($q) => $q->whereHas('customer', fn ($q2) => $q2->where('customer_name', 'ilike', '%'.$this->search.'%')
            ))
            ->when($this->filterSalesman, fn ($q) => $q->where('salesman_id', $this->filterSalesman))
            ->orderBy('salesman_id')
            ->orderBy('day_of_week')
            ->paginate(20);

        $salesmen = User::where('role', 'SALESMAN')->where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('status', 'ACTIVE')->orderBy('customer_name')->get();
        $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

        return view('livewire.admin.visit-schedule-manager', compact('schedules', 'salesmen', 'customers', 'days'))
            ->layout('components.layouts.app', ['title' => 'Jadwal Kunjungan']);
    }
}
