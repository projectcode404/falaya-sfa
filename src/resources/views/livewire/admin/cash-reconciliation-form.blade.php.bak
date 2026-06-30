<div class="container-fluid py-4">
    <h3 class="mb-1">Cash Reconciliation</h3>
    <p class="text-muted">Tanggal operasional: {{ $operationalDate }}</p>

    @if($lastResult)
        <div class="alert {{ $lastResult['status'] === 'RECONCILED' ? 'alert-success' : 'alert-danger' }}">
            <strong>{{ $lastResult['status'] === 'RECONCILED' ? '✅ RECONCILED' : '⚠️ DISCREPANCY' }}</strong><br>
            Sistem: Rp {{ number_format($lastResult['system_total'], 0, ',', '.') }} ·
            Diterima: Rp {{ number_format($lastResult['actual_received'], 0, ',', '.') }} ·
            Selisih: Rp {{ number_format($lastResult['difference'], 0, ',', '.') }}
        </div>
    @endif

    @if($submitError)
        <div class="alert alert-danger">{{ $submitError }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Daftar Salesman</div>
                <div class="list-group list-group-flush">
                    @foreach($salesmanList as $s)
                        <button type="button"
                            wire:click="selectSalesman({{ $s['id'] }})"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $selectedSalesmanId === $s['id'] ? 'active' : '' }}">
                            {{ $s['name'] }}
                            <span class="badge {{ $s['status'] === 'RECONCILED' ? 'bg-success' : ($s['status'] === 'DISCREPANCY' ? 'bg-danger' : 'bg-secondary') }}">
                                {{ $s['status'] }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-8">
            @if($selectedSalesmanId)
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Proses Reconciliation</h5>

                        <table class="table table-sm">
                            <tr>
                                <td>Cash Sales (Sales Order CASH)</td>
                                <td class="text-end">Rp {{ number_format($cashSalesTotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Collection Cash (Payment CASH)</td>
                                <td class="text-end">Rp {{ number_format($collectionCashTotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Total Sistem</td>
                                <td class="text-end">Rp {{ number_format($systemTotal, 0, ',', '.') }}</td>
                            </tr>
                        </table>

                        <div class="mb-3">
                            <label class="form-label">Uang Diterima Fisik (Rp)</label>
                            <input type="number" class="form-control" wire:model="actualReceived" step="1000" placeholder="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan (opsional, wajib jika ada selisih)</label>
                            <textarea class="form-control" wire:model="notes" rows="2"></textarea>
                        </div>

                        <button class="btn btn-primary" wire:click="submitReconciliation" wire:loading.attr="disabled">
                            <span wire:loading wire:target="submitReconciliation">Memproses...</span>
                            <span wire:loading.remove wire:target="submitReconciliation">Proses Reconciliation</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        Pilih salesman dari daftar untuk memproses reconciliation.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
