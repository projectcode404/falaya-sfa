<div>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Manajemen Area</h2>
                </div>
                <div class="col-auto ms-auto">
                    <button class="btn btn-primary" wire:click="openCreate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                        Tambah Area
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
                        <h3 class="card-title">{{ $isEdit ? 'Edit Area' : 'Tambah Area Baru' }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label required">Kode Area</label>
                                <input type="text" class="form-control @error('area_code') is-invalid @enderror"
                                    wire:model="area_code" placeholder="JKT-BARAT">
                                @error('area_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Nama Area</label>
                                <input type="text" class="form-control @error('area_name') is-invalid @enderror"
                                    wire:model="area_name" placeholder="Jakarta Barat">
                                @error('area_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" wire:model="is_active">
                                    <label class="form-check-label">Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Area' }}
                        </button>
                        <button class="btn btn-secondary" wire:click="cancelForm">Batal</button>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
                        </span>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama atau kode area...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Area</th>
                                <th>Jumlah Customer</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($areas as $area)
                                <tr class="{{ $area->trashed() ? 'opacity-50' : '' }}">
                                    <td><code>{{ $area->area_code }}</code></td>
                                    <td>{{ $area->area_name }}</td>
                                    <td>{{ $area->customers()->count() }}</td>
                                    <td>
                                        @if($area->trashed())
                                            <span class="badge bg-secondary">Dihapus</span>
                                        @elseif($area->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-warning">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$area->trashed())
                                            <button class="btn btn-sm btn-outline-primary me-1"
                                                wire:click="openEdit({{ $area->id }})">Edit</button>
                                            <button class="btn btn-sm {{ $area->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                wire:click="toggleActive({{ $area->id }})"
                                                wire:confirm="{{ $area->is_active ? 'Nonaktifkan area ini?' : 'Aktifkan area ini?' }}">
                                                {{ $area->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Belum ada area. Klik "Tambah Area" untuk memulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($areas->hasPages())
                    <div class="card-footer">
                        {{ $areas->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
