<div>
    {{-- ============================================================
         PWA Dashboard — Falaya SFA
         Livewire component (read-only, no Alpine needed here)
         ============================================================ --}}

    <style>
        .progress-ring { transform: rotate(-90deg); }
        .progress-ring__circle { transition: stroke-dashoffset 0.5s ease; }
        .quick-stat-card { border-radius: 12px; border: 1px solid #e6e7e9; background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.08); padding: 16px; }
        .falaya-card { border-radius: 12px; border: 1px solid #e6e7e9; margin-bottom: 12px; background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .falaya-card--warning { border-color: #f59f00; background: #fffbf0; }
        .falaya-card__title { font-weight: 600; font-size: 1rem; color: #1a1a2e; }
        .falaya-card__subtitle { font-size: 0.875rem; color: #616876; }
        .visit-preview-card { border-radius: 10px; border: 1px solid #e6e7e9; background: white; padding: 12px 14px; margin-bottom: 8px; display: flex; align-items: center; gap: 12px; min-height: 56px; text-decoration: none; color: inherit; }
        .visit-preview-card:active { background: #f4f6fb; }
        .badge-planned { background: #e6e7e9; color: #616876; font-size: 0.75rem; padding: 2px 8px; border-radius: 20px; white-space: nowrap; }
        .badge-inprogress { background: #fffbeb; color: #b45309; font-size: 0.75rem; padding: 2px 8px; border-radius: 20px; white-space: nowrap; }
        .section-label { font-size: 0.75rem; font-weight: 600; color: #616876; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px; }
    </style>

    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div class="px-3 pt-3 pb-2">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-0 fw-bold" style="color:#1a1a2e">Halo, {{ $userName }} 👋</h5>
                <p class="mb-0" style="font-size:0.85rem;color:#616876">{{ $today }}</p>
                {{-- $today = e.g. "Sabtu, 27 Juni 2026" — format in component --}}
            </div>
            <x-heroicon-o-cube style="width:24px;height:24px;color:#64748b" />
        </div>
    </div>

    {{-- ── Sync warning (only when pending) ──────────────────────── --}}
    @if ($pendingSyncCount > 0)
    <div class="px-3 mb-1">
        <div class="falaya-card falaya-card--warning p-3 d-flex align-items-center gap-2 mb-0">
            <x-heroicon-o-exclamation-triangle style="width:20px;height:20px;color:#b45309;flex-shrink:0" />
            <div class="flex-grow-1">
                <span style="font-size:0.875rem;font-weight:600;color:#b45309">{{ $pendingSyncCount }} transaksi belum tersinkron</span>
                <div style="font-size:0.8rem;color:#b45309">Ketuk untuk coba kirim ulang</div>
            </div>
            <button wire:click="retrySync" class="btn btn-sm btn-warning" style="min-height:36px;font-size:0.8rem">Kirim</button>
        </div>
    </div>
    @endif

    {{-- ── Visit Progress Ring + Quick Stats ─────────────────────── --}}
    <div class="px-3 mb-3">
        <div class="falaya-card p-3 mb-0">
            <div class="d-flex align-items-center gap-3">
                {{-- SVG Progress Ring --}}
                @php
                    $total = $visitTotal > 0 ? $visitTotal : 1;
                    $pct = round($visitDone / $total * 100);
                    $circumference = 2 * M_PI * 28;
                    $offset = $circumference - ($pct / 100 * $circumference);
                @endphp
                <div style="flex-shrink:0;position:relative;width:72px;height:72px">
                    <svg width="72" height="72" class="progress-ring">
                        <circle cx="36" cy="36" r="28" fill="none" stroke="#e6e7e9" stroke-width="6"/>
                        <circle
                            class="progress-ring__circle"
                            cx="36" cy="36" r="28"
                            fill="none"
                            stroke="#f59e0b"
                            stroke-width="6"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $offset }}"
                        />
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
                        <span style="font-size:1rem;font-weight:700;color:#f59e0b">{{ $pct }}%</span>
                    </div>
                </div>

                <div class="flex-grow-1">
                    <div class="falaya-card__title mb-1">Kunjungan Hari Ini</div>
                    <div style="font-size:1.5rem;font-weight:700;color:#1a1a2e;line-height:1.2">
                        {{ $visitDone }}<span style="font-size:1rem;font-weight:400;color:#616876"> / {{ $visitTotal }} outlet</span>
                    </div>
                    @if ($visitInProgress > 0)
                    <div style="font-size:0.8rem;color:#f59e0b;margin-top:2px">{{ $visitInProgress }} sedang berlangsung</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Quick Stats 2-col ──────────────────────────────────────── --}}
    <div class="px-3 mb-3">
        <div class="row g-2">
            <div class="col-6">
                <div class="quick-stat-card text-center">
                    <div style="margin-bottom:4px;display:flex;justify-content:center"><x-heroicon-o-cube style="width:24px;height:24px;color:#64748b" /></div>
                    <div style="font-size:1.25rem;font-weight:700;color:#1a1a2e">{{ number_format($totalStockQty) }}</div>
                    <div style="font-size:0.75rem;color:#616876">pcs terbawa</div>
                </div>
            </div>
            <div class="col-6">
                <div class="quick-stat-card text-center">
                    <div style="margin-bottom:4px;display:flex;justify-content:center"><x-heroicon-o-shopping-cart style="width:24px;height:24px;color:#64748b" /></div>
                    <div style="font-size:1.25rem;font-weight:700;color:#1a1a2e">{{ $todayOrderCount }}</div>
                    <div style="font-size:0.75rem;color:#616876">transaksi hari ini</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Stok Bawaan mini card ───────────────────────────────────── --}}
    @if (count($stockItems) > 0)
    <div class="px-3 mb-3">
        <div class="section-label d-flex align-items-center gap-1"><x-heroicon-o-cube style="width:14px;height:14px" /> Stok Bawaan</div>
        <div class="falaya-card p-3 mb-0">
            @foreach ($stockItems as $item)
            <div class="d-flex justify-content-between align-items-center {{ !$loop->last ? 'mb-2 pb-2 border-bottom' : '' }}">
                <div>
                    <div style="font-size:0.9rem;font-weight:600;color:#1a1a2e">{{ $item['name'] }}</div>
                    @if (!empty($item['variant']))
                    <div style="font-size:0.78rem;color:#616876">{{ $item['variant'] }}</div>
                    @endif
                </div>
                <div style="font-size:1rem;font-weight:700;color:{{ $item['qty'] <= 5 ? '#d63939' : '#1a1a2e' }}">
                    {{ $item['qty'] }} pcs
                    @if ($item['qty'] <= 5 && $item['qty'] > 0)
                    <x-heroicon-o-exclamation-triangle style="width:12px;height:12px;color:#d63939;display:inline-block;vertical-align:-1px" />
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Visit Preview ───────────────────────────────────────────── --}}
    @if (count($pendingVisits) > 0)
    <div class="px-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="section-label mb-0 d-flex align-items-center gap-1"><x-heroicon-o-map-pin style="width:14px;height:14px" /> Outlet Selanjutnya</div>
            <a href="{{ route('pwa.pages.visits') }}" style="font-size:0.8rem;color:#f59e0b;text-decoration:none;font-weight:600">Lihat Semua →</a>
        </div>

        @foreach ($pendingVisits->take(3) as $visit)
        <a href="{{ route('pwa.pages.visits.detail', $visit['id']) }}" class="visit-preview-card">
            <x-heroicon-o-map-pin style="width:20px;height:20px;color:#64748b;flex-shrink:0" />
            <div class="flex-grow-1 overflow-hidden">
                <div style="font-size:0.9rem;font-weight:600;color:#1a1a2e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $visit['customer_name'] }}</div>
                <div style="font-size:0.78rem;color:#616876">{{ $visit['area_name'] }}</div>
            </div>
            @if ($visit['status'] === 'IN_PROGRESS')
            <span class="badge-inprogress">Berlangsung</span>
            @else
            <span class="badge-planned">Belum</span>
            @endif
            <div style="color:#616876;font-size:0.9rem">›</div>
        </a>
        @endforeach

        @if (count($pendingVisits) > 3)
        <a href="{{ route('pwa.pages.visits') }}" class="d-block text-center py-2" style="font-size:0.85rem;color:#f59e0b;text-decoration:none">
            +{{ count($pendingVisits) - 3 }} outlet lagi →
        </a>
        @endif
    </div>
    @else
    <div class="px-3 mb-3">
        <div style="border-radius:12px;border:1px solid #dcfce7;background:#f4fdf5;padding:20px;text-align:center">
            <div style="font-size:2rem;margin-bottom:8px">🎉</div>
            <div style="font-weight:600;color:#15803d">Semua kunjungan selesai!</div>
            <div style="font-size:0.85rem;color:#15803d;opacity:0.75;margin-top:4px">Kerja bagus hari ini.</div>
        </div>
    </div>
    @endif

    {{-- bottom padding for nav bar --}}
    <div style="height:80px"></div>
</div>
