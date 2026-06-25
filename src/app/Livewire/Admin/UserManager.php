<?php

namespace App\Livewire\Admin;

use App\Models\Area;
use App\Models\SalesmanArea;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterRole = '';

    public bool $showForm = false;

    public bool $isEdit = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = 'SALESMAN';

    public string $password = '';

    public bool $is_active = true;

    // Salesman area assignment
    public array $selectedAreas = [];

    protected function rules(): array
    {
        $uniqueEmail = $this->isEdit
            ? 'unique:users,email,'.$this->editingId.',id,deleted_at,NULL'
            : 'unique:users,email,NULL,id,deleted_at,NULL';

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', $uniqueEmail],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:OWNER,ADMIN,SALESMAN'],
            'password' => [$this->isEdit ? 'nullable' : 'required', 'string', 'min:6'],
            'is_active' => ['boolean'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
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
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->role = $user->role;
        $this->password = '';
        $this->is_active = $user->is_active;
        $this->selectedAreas = $user->activeAreas->pluck('area_id')->map(fn ($id) => (string) $id)->toArray();
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'role' => $this->role,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEdit) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
            $user->syncRoles([$this->role]);
        } else {
            $user = User::create($data);
            $user->assignRole($this->role);
        }

        // Sync salesman areas
        if ($this->role === 'SALESMAN') {
            // Deactivate all existing
            SalesmanArea::where('user_id', $user->id)->update(['is_active' => false]);

            foreach ($this->selectedAreas as $areaId) {
                SalesmanArea::updateOrCreate(
                    ['user_id' => $user->id, 'area_id' => $areaId],
                    ['is_active' => true, 'effective_from' => now()->toDateString(), 'created_by' => auth()->id()]
                );
            }
        }

        session()->flash('success', $this->isEdit ? 'User berhasil diperbarui.' : 'User berhasil ditambahkan.');
        $this->resetForm();
        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);
        session()->flash('success', 'Status user diperbarui.');
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'password', 'selectedAreas']);
        $this->role = 'SALESMAN';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::withTrashed()
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'ilike', '%'.$this->search.'%')
                ->orWhere('email', 'ilike', '%'.$this->search.'%')
            ))
            ->when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
            ->orderBy('name')
            ->paginate(15);

        $areas = Area::where('is_active', true)->orderBy('area_name')->get();

        return view('livewire.admin.user-manager', compact('users', 'areas'))
            ->layout('components.layouts.app', ['title' => 'Manajemen User']);
    }
}
