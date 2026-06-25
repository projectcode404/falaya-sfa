<x-layouts.app heading="Approval">
    <!-- Tab Navigation -->
    <div class="card mb-3">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                @php
                    $tabs = [
                        'customer'   => ['label' => 'Customer Kredit', 'count' => $customerCredits->count()],
                        'override'   => ['label' => 'Override Limit', 'count' => $creditOverrides->count()],
                        'adjustment' => ['label' => 'Stock Adjustment', 'count' => $stockAdjustments->count()],
                        'return'     => ['label' => 'Customer Return', 'count' => $customerReturns->count()],
                        'writeoff'   => ['label' => 'Write-off', 'count' => $stockWriteoffs->count()],
                    ];
                @endphp
                @foreach($tabs as $key => $t)
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === $key ? 'active' : '' }}"
                           href="/owner/approvals?tab={{ $key }}">
                            {{ $t['label'] }}
                            @if($t['count'] > 0)
                                <span class="badge bg-warning ms-1">{{ $t['count'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Customer Kredit --}}
    @if($tab === 'customer')
        @forelse($customerCredits as $c)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-1">{{ $c->customer_name }}</h4>
                            <div class="text-muted small">
                                Diajukan oleh {{ $c->requestedBy->name ?? '-' }} ·
                                {{ $c->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <span class="badge bg-warning-lt">Pending</span>
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm-4"><strong>Tipe:</strong> {{ $c->customer_type }}</div>
                        <div class="col-sm-4"><strong>Limit diminta:</strong> Rp {{ number_format($c->credit_limit, 0, ',', '.') }}</div>
                        <div class="col-sm-4"><strong>Term:</strong> {{ $c->credit_term_days }} hari</div>
                    </div>
                    <div class="mt-1 text-muted small">{{ $c->address }}</div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <form method="POST" action="{{ route('owner.approvals.customer-credit.approve', $c) }}" class="d-flex gap-2 align-items-center flex-grow-1">
                        @csrf
                        <input type="number" name="credit_limit" class="form-control form-control-sm w-auto"
                               value="{{ $c->credit_limit }}" placeholder="Limit disetujui">
                        <button class="btn btn-success btn-sm">✓ Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('owner.approvals.customer-credit.reject', $c) }}" class="d-flex gap-2 align-items-center">
                        @csrf
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Alasan penolakan" required>
                        <button class="btn btn-danger btn-sm">✗ Tolak</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-center text-muted py-4">Tidak ada pengajuan customer kredit.</div></div>
        @endforelse
    @endif

    {{-- Credit Override --}}
    @if($tab === 'override')
        @forelse($creditOverrides as $o)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h4>{{ $o->customer->customer_name ?? '-' }}</h4>
                        <span class="badge bg-warning-lt">Pending</span>
                    </div>
                    <div class="text-muted small mb-3">
                        Diajukan oleh {{ $o->requestedBy->name ?? '-' }} · {{ $o->requested_at->diffForHumans() }}
                    </div>
                    <div class="row">
                        <div class="col-sm-3"><strong>SO:</strong> {{ $o->salesOrder->document_number ?? '-' }}</div>
                        <div class="col-sm-3"><strong>Nilai Order:</strong> Rp {{ number_format($o->order_amount, 0, ',', '.') }}</div>
                        <div class="col-sm-3"><strong>Outstanding:</strong> Rp {{ number_format($o->outstanding_at_request, 0, ',', '.') }}</div>
                        <div class="col-sm-3"><strong>Limit saat ini:</strong> Rp {{ number_format($o->credit_limit_at_request, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <form method="POST" action="{{ route('owner.approvals.credit-override.approve', $o) }}">
                        @csrf
                        <button class="btn btn-success btn-sm">✓ Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('owner.approvals.credit-override.reject', $o) }}" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Alasan" required>
                        <button class="btn btn-danger btn-sm">✗ Tolak</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-center text-muted py-4">Tidak ada override pending.</div></div>
        @endforelse
    @endif

    {{-- Stock Adjustment --}}
    @if($tab === 'adjustment')
        @forelse($stockAdjustments as $a)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h4>{{ $a->document_number }}</h4>
                        <span class="badge bg-warning-lt">Pending</span>
                    </div>
                    <div class="text-muted small mb-3">
                        Diajukan oleh {{ $a->createdBy->name ?? '-' }} · {{ $a->created_at->diffForHumans() }}
                    </div>
                    <div class="row">
                        <div class="col-sm-3"><strong>Produk:</strong> {{ $a->product->product_name ?? '-' }}</div>
                        <div class="col-sm-3"><strong>Qty:</strong> {{ $a->qty }}</div>
                        <div class="col-sm-3"><strong>Alasan:</strong> {{ $a->reason }}</div>
                        <div class="col-sm-3"><strong>Sumber:</strong> {{ $a->source_context }}</div>
                    </div>
                    @if($a->notes)
                        <div class="mt-1 text-muted small">{{ $a->notes }}</div>
                    @endif
                </div>
                <div class="card-footer d-flex gap-2">
                    <form method="POST" action="{{ route('owner.approvals.stock-adjustment.approve', $a) }}">
                        @csrf
                        <button class="btn btn-success btn-sm">✓ Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('owner.approvals.stock-adjustment.reject', $a) }}" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Alasan" required>
                        <button class="btn btn-danger btn-sm">✗ Tolak</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-center text-muted py-4">Tidak ada stock adjustment pending.</div></div>
        @endforelse
    @endif

    {{-- Customer Return --}}
    @if($tab === 'return')
        @forelse($customerReturns as $r)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h4>{{ $r->document_number }}</h4>
                        <span class="badge bg-warning-lt">Pending</span>
                    </div>
                    <div class="text-muted small mb-3">
                        {{ $r->customer->customer_name ?? '-' }} ·
                        Diajukan oleh {{ $r->createdBy->name ?? '-' }} · {{ $r->created_at->diffForHumans() }}
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Invoice:</strong> {{ $r->invoice->invoice_number ?? '-' }}</div>
                        <div class="col-sm-4"><strong>Total:</strong> Rp {{ number_format($r->total_amount, 0, ',', '.') }}</div>
                        <div class="col-sm-4"><strong>Alasan:</strong> {{ $r->reason }}</div>
                    </div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <form method="POST" action="{{ route('owner.approvals.customer-return.approve', $r) }}">
                        @csrf
                        <button class="btn btn-success btn-sm">✓ Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('owner.approvals.customer-return.reject', $r) }}" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Alasan" required>
                        <button class="btn btn-danger btn-sm">✗ Tolak</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-center text-muted py-4">Tidak ada customer return pending.</div></div>
        @endforelse
    @endif

    {{-- Stock Write-off --}}
    @if($tab === 'writeoff')
        @forelse($stockWriteoffs as $w)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h4>{{ $w->document_number }}</h4>
                        <span class="badge bg-warning-lt">Pending</span>
                    </div>
                    <div class="text-muted small mb-3">
                        Diajukan oleh {{ $w->createdBy->name ?? '-' }} · {{ $w->created_at->diffForHumans() }}
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Produk:</strong> {{ $w->product->product_name ?? '-' }}</div>
                        <div class="col-sm-4"><strong>Qty:</strong> {{ $w->qty }}</div>
                        <div class="col-sm-12 mt-1"><strong>Alasan:</strong> {{ $w->reason }}</div>
                    </div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <form method="POST" action="{{ route('owner.approvals.stock-writeoff.approve', $w) }}">
                        @csrf
                        <button class="btn btn-success btn-sm">✓ Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('owner.approvals.stock-writeoff.reject', $w) }}" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Alasan" required>
                        <button class="btn btn-danger btn-sm">✗ Tolak</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-center text-muted py-4">Tidak ada stock write-off pending.</div></div>
        @endforelse
    @endif
</x-layouts.app>
