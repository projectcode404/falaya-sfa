<?php

namespace App\Livewire\Admin;

use App\Models\Area;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AreaManager extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public bool $isEdit = false;

    public ?int $editingId = null;

    public string $area_name = '';

    public string $area_code = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        $uniqueCode = $this->isEdit
            ? 'unique:areas,area_code,'.$this->editingId.',id,deleted_at,NULL'
            : 'unique:areas,area_code,NULL,id,deleted_at,NULL';

        return [
            'area_name' => ['required', 'string', 'max:100'],
            'area_code' => ['required', 'string', 'max:20', $uniqueCode],
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
        $area = Area::findOrFail($id);
        $this->editingId = $area->id;
        $this->area_name = $area->area_name;
        $this->area_code = $area->area_code;
        $this->is_active = $area->is_active;
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'area_name' => $this->area_name,
            'area_code' => strtoupper($this->area_code),
            'is_active' => $this->is_active,
        ];

        if ($this->isEdit) {
            $area = Area::findOrFail($this->editingId);
            $area->update(array_merge($data, [
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]));
            session()->flash('success', 'Area berhasil diperbarui.');
        } else {
            Area::create(array_merge($data, [
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]));
            session()->flash('success', 'Area berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $area = Area::findOrFail($id);
        $area->update([
            'is_active' => ! $area->is_active,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);
        session()->flash('success', 'Status area diperbarui.');
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'area_name', 'area_code']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $areas = Area::withTrashed()
            ->where(function ($q) {
                $q->where('area_name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('area_code', 'ilike', '%'.$this->search.'%');
            })
            ->orderBy('area_name')
            ->paginate(15);

        return view('livewire.admin.area-manager', compact('areas'))
            ->layout('components.layouts.app', ['title' => 'Manajemen Area']);
    }
}
