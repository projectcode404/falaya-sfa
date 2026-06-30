<div>
    {{-- ============================================================
         PWA Visit List — Falaya SFA
         Livewire component (read-only list)
         ============================================================ --}}

    <style>
        .section-label { font-size: 0.75rem; font-weight: 600; color: #616876; text-transform: uppercase; letter-spacing: 0.04em; padding: 0 16px; margin-bottom: 6px; margin-top: 16px; display: block; }
        .visit-card { display: flex; align-items: center; gap: 12px; padding: 12px 14px; min-height: 70px; text-decoration: none; color: inherit; border-bottom: 1px solid #f0f0f0; }
        .visit-card:last-child { border-bottom: none; }
        .visit-card:active { background: #f4f6fb; }
        .visit-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        /* Status badge variants */
        .bs-planned    { background:#e6e7e9;color:#616876; }
        .bs-inprogress { background:#fffbeb;color:#b45309; }
        .bs-completed  { background:#dcfce7;color:#15803d; }
        .bs-noorder    { background:#fef9c3;color:#854d0e; }
        .bs-closed     { background:#e6e7e9;color:#374151; }
        .bs-skipped    { background:#fef2f2;color:#991b1b; }
        .status-badge  { font-size:0.7rem;font-weight:600;padding:3px 8px;border-radius:20px;white-space:nowrap; }
        .section-group { background:white;border-radius:12px;border:1px solid #e6e7e9;box-shadow:0 1px 4px rgba(0,0,0,0.08);overflow:hidden;margin:0 12px 12px; }
        .section-toggle { display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#f8f9fa;border-bottom:1px solid #e6e7e9;cursor:pointer;user-select:none; }
        .section-toggle-label { font-size:0.8rem;font-weight:600;color:#374151; }
        .section-toggle-count { font-size:0.75rem;color:#616876;background:#e6e7e9;padding:2px 8px;border-radius:20px; }
        .empty-state { text-align:center;padding:40px 20px;color:#616876; }
    </style>

    {{-- ── Page Header ─────────────────────────────────────────────── --}}
    <div class="px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0" style="color:#1a1a2e">Kunjungan Hari Ini</h5>
        <p class="mb-0" style="font-size:0.85rem;color:#616876">{{ $today }}</p>
    </div>

    {{-- ── Section: Belum Dikunjungi ─────────────────────────────── --}}
    @php
        $pending  = $visits->whereIn('status', ['PLANNED', 'IN_PROGRESS']);
        $done     = $visits->whereIn('status', ['COMPLETED', 'NO_ORDER', 'OUTLET_CLOSED', 'SKIPPED']);
    @endphp

    @if ($pending->count() > 0)
    <span class="section-label d-flex align-items-center gap-1"><x-heroicon-o-map-pin style="width:13px;height:13px" /> Belum Dikunjungi ({{ $pending->count() }})</span>
    <div class="section-group">
        @foreach ($pending->sortByDesc(fn($v) => $v['status'] === 'IN_PROGRESS') as $visit)
        <a href="{{ route('pwa.pages.visits.detail', $visit['id']) }}" class="visit-card d-flex">
            {{-- icon --}}
            <div class="visit-icon" style="background:{{ $visit['status'] === 'IN_PROGRESS' ? '#fffbeb' : '#f4f6fb' }}">
                @if ($visit['status'] === 'IN_PROGRESS')
                    <x-heroicon-o-signal style="width:20px;height:20px;color:#b45309" />
                @else
                    <x-heroicon-o-map-pin style="width:20px;height:20px;color:#64748b" />
                @endif
            </div>

            {{-- info --}}
            <div class="flex-grow-1 overflow-hidden">
                <div style="font-size:0.92rem;font-weight:600;color:#1a1a2e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $visit['customer_name'] }}</div>
                <div style="font-size:0.78rem;color:#616876;margin-top:1px">{{ $visit['area_name'] }}</div>
                @if ($visit['has_outstanding'])
                <div style="font-size:0.72rem;color:#d97706;margin-top:2px;display:flex;align-items:center;gap:3px"><x-heroicon-o-banknotes style="width:12px;height:12px" /> Ada tagihan</div>
                @endif
            </div>

            {{-- badge --}}
            <div class="d-flex flex-column align-items-end gap-1 ms-1">
                @if ($visit['status'] === 'IN_PROGRESS')
                <span class="status-badge bs-inprogress">Berlangsung</span>
                @else
                <span class="status-badge bs-planned">Belum</span>
                @endif
                <span style="color:#c0c4cc;font-size:0.85rem">›</span>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="mx-3 mb-3">
        <div style="border-radius:12px;border:1px solid #dcfce7;background:#f4fdf5;padding:20px;text-align:center">
            <div style="font-size:2rem;margin-bottom:8px">🎉</div>
            <div style="font-weight:600;color:#15803d">Semua outlet sudah dikunjungi!</div>
        </div>
    </div>
    @endif

    {{-- ── Section: Sudah Selesai (collapsible) ───────────────────── --}}
    @if ($done->count() > 0)
    <div x-data="{ open: false }" class="mx-3 mb-3" style="border-radius:12px;border:1px solid #e6e7e9;background:white;box-shadow:0 1px 4px rgba(0,0,0,0.08);overflow:hidden">
        <div class="section-toggle" @click="open = !open">
            <span class="section-toggle-label d-flex align-items-center gap-1"><x-heroicon-o-check-circle style="width:14px;height:14px;color:#15803d" /> Sudah Selesai</span>
            <div class="d-flex align-items-center gap-2">
                <span class="section-toggle-count">{{ $done->count() }}</span>
                <span style="font-size:0.8rem;color:#616876;transition:transform 0.2s" :style="open ? 'transform:rotate(180deg)' : ''">▾</span>
            </div>
        </div>

        <div x-show="open" x-collapse>
            @foreach ($done as $visit)
            <a href="{{ route('pwa.pages.visits.detail', $visit['id']) }}" class="visit-card d-flex">
                <div class="visit-icon" style="background:#f4f6fb">
                    @switch($visit['status'])
                        @case('COMPLETED')  <x-heroicon-o-shopping-cart style="width:18px;height:18px;color:#64748b" /> @break
                        @case('NO_ORDER')   <x-heroicon-o-hand-raised style="width:18px;height:18px;color:#64748b" /> @break
                        @case('OUTLET_CLOSED') <x-heroicon-o-lock-closed style="width:18px;height:18px;color:#64748b" /> @break
                        @case('SKIPPED')    <x-heroicon-o-forward style="width:18px;height:18px;color:#64748b" /> @break
                    @endswitch
                </div>

                <div class="flex-grow-1 overflow-hidden">
                    <div style="font-size:0.92rem;font-weight:600;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $visit['customer_name'] }}</div>
                    <div style="font-size:0.78rem;color:#616876;margin-top:1px">{{ $visit['area_name'] }}</div>
                </div>

                <div class="d-flex flex-column align-items-end gap-1 ms-1">
                    @switch($visit['status'])
                        @case('COMPLETED')
                            <span class="status-badge bs-completed">Ada Order</span> @break
                        @case('NO_ORDER')
                            <span class="status-badge bs-noorder">Tanpa Order</span> @break
                        @case('OUTLET_CLOSED')
                            <span class="status-badge bs-closed">Tutup</span> @break
                        @case('SKIPPED')
                            <span class="status-badge bs-skipped">Terlewat</span> @break
                    @endswitch
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Empty state (no visits at all) ─────────────────────────── --}}
    @if ($visits->count() === 0)
    <div class="empty-state">
        <div style="margin-bottom:12px;display:flex;justify-content:center"><x-heroicon-o-clipboard-document-list style="width:40px;height:40px;color:#94a3b8" /></div>
        <div style="font-weight:600;margin-bottom:4px">Belum ada kunjungan hari ini</div>
        <div style="font-size:0.85rem">Admin belum menyiapkan jadwal kunjungan.</div>
    </div>
    @endif

    {{-- ── FAB: Kunjungan Tidak Terjadwal ────────────────────────── --}}
    <button
        class="fab"
        onclick="window.location.href='{{ route('pwa.pages.visits.unplanned') }}'"
        title="Kunjungan Tidak Terjadwal"
    >＋</button>

    <div style="height:80px"></div>
</div>
