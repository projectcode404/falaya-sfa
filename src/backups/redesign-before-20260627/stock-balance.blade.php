<div>
    <div class="pwa-header">
        <h5>📦 Stok Bawaan</h5>
        <small>Total: {{ number_format($totalQty, 0) }} pcs</small>
    </div>
    <div class="px-3">
        @forelse($stok as $s)
            <div class="falaya-card {{ $s->qty <= 5 ? 'falaya-card--warning' : '' }}">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="falaya-card__title">{{ $s->product->product_name }}</div>
                        @if($s->product->variant)
                            <div class="falaya-card__subtitle">{{ $s->product->variant }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <strong class="{{ $s->qty <= 5 ? 'text-warning' : 'text-success' }}" style="font-size:1.3rem">
                            {{ number_format($s->qty, 0) }}
                        </strong>
                        <div class="falaya-card__subtitle">{{ $s->product->unit }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="falaya-card falaya-card--warning">
                <div class="card-body text-center py-4">
                    <div style="font-size:2rem">📭</div>
                    <div class="falaya-card__subtitle mt-2">Stok bawaan habis. Hubungi Admin untuk loading tambahan.</div>
                </div>
            </div>
        @endforelse
    </div>
</div>
