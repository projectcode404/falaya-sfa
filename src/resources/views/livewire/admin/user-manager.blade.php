<div>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Manajemen User</h2>
                </div>
                <div class="col-auto ms-auto">
                    <button class="btn btn-primary" wire:click="openCreate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                        Tambah User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible mb-3">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($showForm)
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ $isEdit ? 'Edit User' : 'Tambah User Baru' }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label required">Nama</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    wire:model="name" placeholder="Budi Santoso">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    wire:model="email" placeholder="budi@falaya.test">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. HP</label>
                                <input type="text" class="form-control" wire:model="phone" placeholder="08123456789">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">Role</label>
                                <select class="form-select" wire:model.live="role">
                                    <option value="SALESMAN">Salesman</option>
                                    <option value="ADMIN">Admin</option>
                                    <option value="OWNER">Owner</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label {{ $isEdit ? '' : 'required' }}">
                                    Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}
                                </label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    wire:model="password" placeholder="Min. 6 karakter">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" wire:model="is_active">
                                    <label class="form-check-label">Aktif</label>
                                </div>
                            </div>

                            @if($role === 'SALESMAN')
                                <div class="col-12">
                                    <label class="form-label">Area Tugas</label>
                                    <div class="row g-2">
                                        @foreach($areas as $area)
                                            <div class="col-md-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        wire:model="selectedAreas" value="{{ $area->id }}">
                                                    <span class="form-check-label">{{ $area->area_name }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah User' }}
                        </button>
                        <button class="btn btn-secondary" wire:click="cancelForm">Batal</button>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header gap-2 flex-wrap">
                    <div class="input-group" style="max-width:300px">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
                        </span>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama atau email...">
                    </div>
                    <select class="form-select" style="max-width:150px" wire:model.live="filterRole">
                        <option value="">Semua Role</option>
                        <option value="OWNER">Owner</option>
                        <option value="ADMIN">Admin</option>
                        <option value="SALESMAN">Salesman</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Area</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="{{ $user->trashed() ? 'opacity-50' : '' }}">
                                    <td>{{ $user->name }}</td>
                                    <td><small>{{ $user->email }}</small></td>
                                    <td>
                                        @php
                                            $roleClass = match($user->role) {
                                                'OWNER' => 'bg-purple',
                                                'ADMIN' => 'bg-blue',
                                                default => 'bg-cyan',
                                            };
                                        @endphp
                                        <span class="badge {{ $roleClass }} text-white">{{ $user->role }}</span>
                                    </td>
                                    <td>
                                        @if($user->role === 'SALESMAN')
                                            @foreach($user->activeAreas as $sa)
                                                <span class="badge bg-light text-dark border">{{ $sa->area->area_name ?? '-' }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->trashed())
                                            <span class="badge bg-secondary">Dihapus</span>
                                        @elseif($user->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-warning">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$user->trashed())
                                            <button class="btn btn-sm btn-outline-primary me-1"
                                                wire:click="openEdit({{ $user->id }})">Edit</button>
                                            <button class="btn btn-sm {{ $user->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                wire:click="toggleActive({{ $user->id }})"
                                                wire:confirm="{{ $user->is_active ? 'Nonaktifkan user ini?' : 'Aktifkan user ini?' }}">
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($users->hasPages())
                    <div class="card-footer">{{ $users->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</div>
