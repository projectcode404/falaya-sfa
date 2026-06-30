<div>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Stock Unloading</h2>
                </div>
                <div class="col-auto ms-auto">
                    <button class="btn btn-primary" wire:click="openCreate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                        Proses Unloading
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
                        <h3 class="card-title">Proses Stock Unloading</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
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

                        @if($salesman_id && count($items) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Stok Dibawa</th>
                                            <th style="width:150px">Qty Kembali</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $index => $item)
                                            <tr>
                                                <td>
                                                    {{ $item['product_name'] }}
                                                    <small class="text-muted">({{ $item['unit'] }})</small>
                                                </td>
                                                <td class="fw-bold">{{ number_format($item['max_qty'], 0) }}</td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm"
                                                        wire:model="items.{{ $index }}.qty"
                                                        min="0" max="{{ $item['max_qty'] }}" step="1">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($salesman_id)
                            <div class="alert alert-info">Salesman ini tidak memiliki stok bawaan hari ini.</div>
                        @else
                            <p class="text-muted">Pilih salesman terlebih dahulu.</p>
                        @endif
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            Post Unloading
                        </button>
                        <button class="btn btn-secondary" wire:click="cancelForm">Batal</button>
                    </div>
                </div>
            @endif

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
                            @forelse($unloadings as $unloading)
                                <tr>
                                    <td><code>{{ $unloading->document_number }}</code></td>
                                    <td>{{ $unloading->salesman->name ?? '-' }}</td>
                                    <td>{{ $unloading->operational_date }}</td>
                                    <td>
                                        @foreach($unloading->items as $item)
                                            <small class="d-block">{{ $item->product->product_name ?? '-' }}: {{ number_format($item->qty, 0) }}</small>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($unloading->status === 'POSTED')
                                            <span class="badge bg-success">POSTED</span>
                                        @else
                                            <span class="badge bg-secondary">DRAFT</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada stock unloading.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($unloadings->hasPages())
                    <div class="card-footer">{{ $unloadings->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</div>
