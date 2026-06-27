<div>
    <div class="pwa-header">
        <h5>Halo, {{ $namaUser }} 👋</h5>
        <small>{{ $today->translatedFormat('l, d F Y') }}</small>
    </div>
    <div class="px-3">
        <div class="falaya-card {{ $stokBawaan->isEmpty() ? 'falaya-card--warning' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="falaya-card__title">📦 Stok Bawaan Hari Ini</div>
                    <a href="/pwa/stock" class="btn btn-sm btn-outline-primary py-1">Detail →</a>
                </div>
                @forelse($stokBawaan as $s)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="falaya-card__subtitle">{{ $s->product->product_name }}</span>
                        <strong>{{ number_format($s->qty, 0) }} {{ $s->product->unit }}</strong>
                    </div>
                @empty
                    <p class="falaya-card__subtitle mb-0">Stok bawaan habis. Hubungi Admin untuk loading tambahan.</p>
                @endforelse
            </div>
        </div>
        <div class="falaya-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="falaya-card__title">📍 Kunjungan Hari Ini</div>
                    <a href="/pwa/visits" class="btn btn-sm btn-outline-primary py-1">Lihat →</a>
                </div>
                <div class="falaya-card__subtitle mb-2">{{ $visitSelesai }} dari {{ $totalVisit }} outlet selesai</div>
                @if($totalVisit > 0)
                    <div class="progress mb-1">
                        <div class="progress-bar bg-success" style="width: {{ $visitProgress }}%"></div>
                    </div>
                    <small class="text-muted">{{ $visitProgress }}% selesai</small>
                @else
                    <small class="text-muted">Belum ada kunjungan terjadwal hari ini.</small>
                @endif
                @if($visitBelum->isNotEmpty())
                    <div class="mt-2">
                        <small class="text-muted d-block mb-1">Belum dikunjungi:</small>
                        @foreach($visitBelum->take(3) as $v)
                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $v->customer->customer_name }}</span>
                        @endforeach
                        @if($visitBelum->count() > 3)
                            <span class="badge bg-secondary">+{{ $visitBelum->count() - 3 }} lainnya</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="d-grid mt-2">
            <a href="/pwa/visits/unplanned" class="btn btn-outline-secondary">+ Kunjungan Tidak Terjadwal</a>
        </div>
    </div>
</div>
