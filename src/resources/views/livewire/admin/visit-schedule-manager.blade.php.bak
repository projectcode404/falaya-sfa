<div>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Jadwal Kunjungan</h2>
                </div>
                <div class="col-auto ms-auto">
                    <button class="btn btn-primary" wire:click="openCreate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                        Tambah Jadwal
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
                        <h3 class="card-title">{{ $isEdit ? 'Edit Jadwal' : 'Tambah Jadwal Kunjungan' }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label required">Salesman</label>
                                <select class="form-select @error('salesman_id') is-invalid @enderror" wire:model="salesman_id">
                                    <option value="">-- Pilih Salesman --</option>
                                    @foreach($salesmen as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                @error('salesman_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Outlet</label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" wire:model="customer_id">
                                    <option value="">-- Pilih Outlet --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Hari Kunjungan</label>
                                <select class="form-select @error('day_of_week') is-invalid @enderror" wire:model="day_of_week">
                                    <option value="">-- Pilih Hari --</option>
                                    @foreach($days as $num => $label)
                                        <option value="{{ $num }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('day_of_week') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">Berlaku Dari</label>
                                <input type="date" class="form-control @error('effective_from') is-invalid @enderror"
                                    wire:model="effective_from">
                                @error('effective_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Berlaku Sampai</label>
                                <input type="date" class="form-control @error('effective_to') is-invalid @enderror"
                                    wire:model="effective_to">
                                <small class="text-muted">Kosongkan = aktif tanpa batas</small>
                                @error('effective_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
                        </button>
                        <button class="btn btn-secondary" wire:click="cancelForm">Batal</button>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header gap-2 flex-wrap">
                    <div class="input-group" style="max-width:280px">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
                        </span>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama outlet...">
                    </div>
                    <select class="form-select" style="max-width:200px" wire:model.live="filterSalesman">
                        <option value="">Semua Salesman</option>
                        @foreach($salesmen as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Salesman</th>
                                <th>Outlet</th>
                                <th>Hari</th>
                                <th>Berlaku</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->salesman->name ?? '-' }}</td>
                                    <td>{{ $schedule->customer->customer_name ?? '-' }}</td>
                                    <td>{{ $days[$schedule->day_of_week] ?? '-' }}</td>
                                    <td>
                                        <small>
                                            {{ $schedule->effective_from->format('d/m/Y') }}
                                            @if($schedule->effective_to)
                                                – {{ $schedule->effective_to->format('d/m/Y') }}
                                            @else
                                                – selamanya
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        @if($schedule->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            wire:click="openEdit({{ $schedule->id }})">Edit</button>
                                        <button class="btn btn-sm {{ $schedule->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            wire:click="toggleActive({{ $schedule->id }})"
                                            wire:confirm="{{ $schedule->is_active ? 'Nonaktifkan jadwal ini?' : 'Aktifkan jadwal ini?' }}">
                                            {{ $schedule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Belum ada jadwal kunjungan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($schedules->hasPages())
                    <div class="card-footer">{{ $schedules->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</div>
