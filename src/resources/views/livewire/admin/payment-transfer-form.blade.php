<div class="container-fluid py-4">
    <h3 class="mb-1">Payment Transfer</h3>
    <p class="text-muted">Verifikasi mutasi bank dulu sebelum input pembayaran ini.</p>

    @if($submitSuccess)
        <div class="alert alert-success">{{ $submitSuccess }}</div>
    @endif

    @if($submitError)
        <div class="alert alert-danger">{{ $submitError }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <input type="text" class="form-control" wire:model.live.debounce.400ms="search" placeholder="Cari customer CREDIT...">
                </div>
                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    @forelse($customers as $c)
                        <button type="button"
                            wire:click="selectCustomer({{ $c['id'] }})"
                            class="list-group-item list-group-item-action {{ $selectedCustomerId === $c['id'] ? 'active' : '' }}">
                            <div class="fw-bold">{{ $c['customer_name'] }}</div>
                            <small class="text-muted">{{ $c['customer_code'] }}</small>
                        </button>
                    @empty
                        <div class="list-group-item text-muted">Tidak ada customer ditemukan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-8">
            @if($selectedCustomerId)
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">{{ $selectedCustomerName }}</h5>

                        @if(count($invoices) === 0)
                            <p class="text-muted">Tidak ada invoice outstanding untuk customer ini.</p>
                        @else
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Invoice</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                        <th class="text-end">Jumlah Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $inv)
                                        <tr wire:key="inv-{{ $inv['id'] }}">
                                            <td>
                                                <input type="checkbox"
                                                    wire:click="toggleSelect({{ $inv['id'] }})"
                                                    {{ ($selected[$inv['id']] ?? false) ? 'checked' : '' }}>
                                            </td>
                                            <td>{{ $inv['invoice_number'] }}</td>
                                            <td>{{ $inv['due_date'] }}</td>
                                            <td>
                                                <span class="badge {{ $inv['status'] === 'OVERDUE' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                    {{ $inv['status'] }}
                                                </span>
                                            </td>
                                            <td style="max-width: 150px">
                                                @if($selected[$inv['id']] ?? false)
                                                    <input type="number" class="form-control form-control-sm text-end"
                                                        wire:model.lazy="amounts.{{ $inv['id'] }}"
                                                        min="0" max="{{ $inv['remaining_amount'] }}" step="1000">
                                                @else
                                                    <span class="text-muted">Rp {{ number_format($inv['remaining_amount'], 0, ',', '.') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3">
                                <strong>Total Transfer</strong>
                                <strong class="fs-5">Rp {{ number_format($this->getTotal(), 0, ',', '.') }}</strong>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Catatan (no. referensi transfer, dll)</label>
                                <input type="text" class="form-control" wire:model="notes" placeholder="Ref BCA #123456">
                            </div>

                            <button class="btn btn-primary" wire:click="submitTransfer" wire:loading.attr="disabled">
                                <span wire:loading wire:target="submitTransfer">Memproses...</span>
                                <span wire:loading.remove wire:target="submitTransfer">Simpan Payment Transfer</span>
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        Cari dan pilih customer untuk mencatat payment transfer.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
