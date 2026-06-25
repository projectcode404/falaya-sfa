<x-layouts.app heading="Dashboard Owner">
    <!-- Stat Cards -->
    <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Penjualan Hari Ini</div>
                    </div>
                    <div class="h1 mb-3">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</div>
                    <div class="d-flex mb-2">
                        <div>Sales Order POSTED hari ini</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Outstanding Piutang</div>
                    <div class="h1 mb-3">Rp {{ number_format($outstandingPiutang, 0, ',', '.') }}</div>
                    <div>Total invoice belum lunas</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card {{ $invoiceOverdue > 0 ? 'border-danger' : '' }}">
                <div class="card-body">
                    <div class="subheader">Invoice Overdue</div>
                    <div class="h1 mb-3 {{ $invoiceOverdue > 0 ? 'text-danger' : '' }}">{{ $invoiceOverdue }}</div>
                    <div>Invoice melewati jatuh tempo</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card {{ $approvalPending > 0 ? 'border-warning' : '' }}">
                <div class="card-body">
                    <div class="subheader">Approval Pending</div>
                    <div class="h1 mb-3 {{ $approvalPending > 0 ? 'text-warning' : '' }}">{{ $approvalPending }}</div>
                    <a href="/owner/approvals" class="small">Lihat semua →</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <!-- Performa Salesman -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Performa Salesman — Hari Ini</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Salesman</th>
                                <th>Compliance</th>
                                <th>Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesmen as $s)
                                <tr>
                                    <td>{{ $s['name'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">{{ $s['compliance'] }}%</span>
                                            <div class="progress flex-grow-1" style="height: 6px">
                                                <div class="progress-bar {{ $s['compliance'] >= 80 ? 'bg-success' : 'bg-warning' }}"
                                                     style="width: {{ $s['compliance'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rp {{ number_format($s['omzet'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Belum ada data salesman aktif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stok & Approval -->
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Stok Gudang</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">GOOD</span>
                        <strong class="text-success">{{ number_format($gudangGood, 0, ',', '.') }} pcs</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">BAD</span>
                        <strong class="{{ $gudangBad > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ number_format($gudangBad, 0, ',', '.') }} pcs
                        </strong>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Approval Terbaru</h3>
                    <a href="/owner/approvals" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($approvalTerbaru as $item)
                        <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-warning-lt me-2">{{ $item['type'] }}</span>
                                <small class="text-muted">{{ $item['by'] }}</small>
                            </div>
                            <div class="mt-1 small">{{ $item['label'] }}</div>
                        </a>
                    @empty
                        <div class="list-group-item text-center text-muted py-3">
                            Tidak ada pengajuan pending.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
