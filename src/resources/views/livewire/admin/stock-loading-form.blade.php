<div>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Stock Loading</h2>
                </div>
                <div class="col-auto ms-auto">
                    <button class="btn btn-primary" wire:click="openCreate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                        Buat Loading
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
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible mb-3">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($showForm)
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Buat Stock Loading Baru</h3>
                    </div>
                    <div class="card-body">

                        {{-- Pilih Salesman --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label required">Salesman</label>
                                <select class="form-select @error('salesman_id') is-invalid @enderror"
                                    wire:model.live="salesman_id">
                                    <option value="">-- Pilih Salesman --</option>
                                    @foreach($salesmen as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                @error('salesman_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @if($salesman_id)
                            {{-- Search Produk --}}
                            <div class="mb-3">
                                <label class="form-label">Tambah Produk</label>
                                <div class="position-relative" style="max-width: 450px;">
                                    <input type="text"
                                        class="form-control"
                                        wire:model.live.debounce.300ms="productSearch"
                                        placeholder="Cari nama produk atau varian..."
                                        autocomplete="off">

                                    @if($showSearchResults)
                                        <div class="dropdown-menu show w-100" style="max-height: 280px; overflow-y:auto;">
                                            @foreach($searchResults as $result)
                                                <button type="button"
                                                    class="dropdown-item d-flex justify-content-between align-items-center py-2"
                                                    wire:click="addProduct({{ $result['product_id'] }})">
                                                    <div>
                                                        <span class="fw-medium">{{ $result['product_name'] }}</span>
                                                        @if($result['variant'])
                                                            <span class="text-muted"> — {{ $result['variant'] }}</span>
                                                        @endif
                                                        <small class="text-muted d-block">({{ $result['unit'] }})</small>
                                                    </div>
                                                    <span class="badge {{ $result['gudang_qty'] > 0 ? 'bg-success-lt' : 'bg-danger-lt' }} ms-2">
                                                        Stok: {{ number_format($result['gudang_qty'], 0) }}
                                                    </span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(strlen(trim($productSearch)) >= 2 && !$showSearchResults)
                                        <div class="dropdown-menu show w-100">
                                            <span class="dropdown-item text-muted">Tidak ada produk ditemukan.</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-hint">Ketik minimal 2 karakter untuk mencari produk.</div>
                            </div>

                            {{-- Tabel item yang sudah dipilih --}}
                            @error('items') <div class="alert alert-warning py-2 mb-3">{{ $message }}</div> @enderror

                            @if(count($items) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th class="text-center" style="width:120px">Stok Gudang</th>
                                                <th style="width:150px">Qty Loading</th>
                                                <th style="width:50px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $index => $item)
                                                <tr>
                                                    <td>
                                                        <span class="fw-medium">{{ $item['product_name'] }}</span>
                                                        @if($item['variant'])
                                                            <span class="text-muted"> — {{ $item['variant'] }}</span>
                                                        @endif
                                                        <small class="text-muted d-block">({{ $item['unit'] }})</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="{{ $item['gudang_qty'] <= 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                                            {{ number_format($item['gudang_qty'], 0) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm @error('items.'.$index.'.qty') is-invalid @enderror"
                                                            wire:model="items.{{ $index }}.qty"
                                                            min="0"
                                                            max="{{ $item['gudang_qty'] }}"
                                                            step="1"
                                                            {{ $item['gudang_qty'] <= 0 ? 'disabled' : '' }}>
                                                        @error('items.'.$index.'.qty')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm btn-ghost-danger"
                                                            wire:click="removeItem({{ $index }})"
                                                            title="Hapus">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-muted py-3">
                                    Belum ada produk ditambahkan. Cari dan pilih produk di atas.
                                </div>
                            @endif
                        @else
                            <p class="text-muted">Pilih salesman terlebih dahulu.</p>
                        @endif
                    </div>

                    <div class="card-footer d-flex gap-2">
                        <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled"
                            {{ count($items) === 0 ? 'disabled' : '' }}>
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            Post Loading
                        </button>
                        <button class="btn btn-secondary" wire:click="cancelForm">Batal</button>
                    </div>
                </div>
            @endif

            {{-- List Stock Loading --}}
            <div class="card">
                <div class="card-header">
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
                                <th>No. Dokumen</th>
                                <th>Salesman</th>
                                <th>Tgl Operasional</th>
                                <th>Item</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loadings as $loading)
                                <tr>
                                    <td><code>{{ $loading->document_number }}</code></td>
                                    <td>{{ $loading->salesman->name ?? '-' }}</td>
                                    <td>{{ $loading->operational_date }}</td>
                                    <td>
                                        @foreach($loading->items as $item)
                                            <small class="d-block">
                                                {{ $item->product->product_name ?? '-' }}
                                                @if($item->product?->variant)
                                                    <span class="text-muted">— {{ $item->product->variant }}</span>
                                                @endif
                                                : {{ number_format($item->qty, 0) }}
                                            </small>
                                        @endforeach
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($loading->status) {
                                                'POSTED'    => 'bg-success',
                                                'CANCELLED' => 'bg-danger',
                                                default     => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $loading->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada stock loading.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($loadings->hasPages())
                    <div class="card-footer">{{ $loadings->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</div>
