<div>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Manajemen Produk</h2>
                </div>
                <div class="col-auto ms-auto">
                    <button class="btn btn-primary" wire:click="openCreate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                        Tambah Produk
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

            {{-- Form tambah/edit --}}
            @if($showForm)
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ $isEdit ? 'Edit Produk' : 'Tambah Produk Baru' }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label required">Kode Produk</label>
                                <input type="text" class="form-control @error('product_code') is-invalid @enderror"
                                    wire:model="product_code" placeholder="PRD-001">
                                @error('product_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label required">Nama Produk</label>
                                <input type="text" class="form-control @error('product_name') is-invalid @enderror"
                                    wire:model="product_name" placeholder="Keripik Singkong Original">
                                @error('product_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Varian</label>
                                <input type="text" class="form-control" wire:model="variant" placeholder="Original, Balado, ...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kategori</label>
                                <input type="text" class="form-control" wire:model="category" placeholder="Keripik">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">Satuan</label>
                                <input type="text" class="form-control @error('unit') is-invalid @enderror"
                                    wire:model="unit" placeholder="pcs, dus, ...">
                                @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">Harga Jual</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('selling_price') is-invalid @enderror"
                                        wire:model="selling_price" min="0" step="100">
                                    @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
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
                            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Produk' }}
                        </button>
                        <button class="btn btn-secondary" wire:click="cancelForm">Batal</button>
                    </div>
                </div>
            @endif

            {{-- Search --}}
            <div class="card">
                <div class="card-header">
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
                        </span>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama atau kode produk...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Produk</th>
                                <th>Varian</th>
                                <th>Satuan</th>
                                <th>Harga Jual</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr class="{{ $product->trashed() ? 'opacity-50' : '' }}">
                                    <td><code>{{ $product->product_code }}</code></td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->variant ?? '-' }}</td>
                                    <td>{{ $product->unit }}</td>
                                    <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                    <td>
                                        @if($product->trashed())
                                            <span class="badge bg-secondary">Dihapus</span>
                                        @elseif($product->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-warning">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$product->trashed())
                                            <button class="btn btn-sm btn-outline-primary me-1"
                                                wire:click="openEdit({{ $product->id }})">Edit</button>
                                            <button class="btn btn-sm {{ $product->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                wire:click="toggleActive({{ $product->id }})"
                                                wire:confirm="{{ $product->is_active ? 'Nonaktifkan produk ini?' : 'Aktifkan produk ini?' }}">
                                                {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Belum ada produk. Klik "Tambah Produk" untuk memulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($products->hasPages())
                    <div class="card-footer">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
