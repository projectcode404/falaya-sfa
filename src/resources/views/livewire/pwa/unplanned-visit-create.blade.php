<div>
    {{-- ============================================================
         PWA Unplanned Visit — Falaya SFA
         Livewire native. Search customer -> pilih -> konfirmasi terpisah.
         Sesuai PRD Bagian 8.2: is_planned = false, salesman bebas pilih
         outlet manapun (status ACTIVE).
         ============================================================ --}}
    <style>
        .search-input { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 0.9rem; color: #374151; }
        .search-input:focus { outline: none; border-color: #206bc4; box-shadow: 0 0 0 3px rgba(32,107,196,0.12); }
        .customer-row { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-bottom: 1px solid #f0f0f0; cursor: pointer; }
        .customer-row:last-child { border-bottom: none; }
        .customer-row:active { background: #f4f6fb; }
        .customer-row--selected { background: #f0f6ff; }
        .confirm-bar { position: fixed; bottom: var(--pwa-navbar-h, 64px); left: 0; right: 0; background: white; border-top: 1px solid #e6e7e9; padding: 12px 16px; z-index: 110; box-shadow: 0 -2px 8px rgba(0,0,0,0.08); }
        .btn-submit { width: 100%; min-height: 50px; border-radius: 10px; border: none; background: #206bc4; color: white; font-weight: 700; font-size: 1rem; }
        .btn-submit:disabled { background: #9ca3af; }
    </style>

    <a href="{{ route('pwa.pages.visits') }}" class="back-btn">‹ Daftar Kunjungan</a>

    <div class="px-3 pt-1">
        <div class="mb-3">
            <h5 class="fw-bold mb-1" style="color:#1a1a2e">Kunjungan Tidak Terjadwal</h5>
            <p class="mb-0" style="font-size:0.85rem;color:#616876">Pilih outlet yang ingin dikunjungi di luar jadwal hari ini.</p>
        </div>

        @if ($submitError)
        <div class="falaya-card falaya-card--danger p-3 mb-3">
            <div style="font-size:0.85rem;color:#991b1b">⚠️ {{ $submitError }}</div>
        </div>
        @endif

        @if ($selectedCustomer)
        {{-- ── Outlet terpilih, siap konfirmasi ──────────────────── --}}
        <div class="falaya-card falaya-card--info p-3 mb-3">
            <div class="falaya-card__title mb-1">🏪 {{ $selectedCustomer->customer_name }}</div>
            <div class="falaya-card__subtitle mb-2">{{ $selectedCustomer->area->area_name ?? '-' }} · {{ $selectedCustomer->address }}</div>
            <button
                type="button"
                wire:click="clearSelection"
                style="font-size:0.8rem;color:#206bc4;background:none;border:none;padding:0;text-decoration:underline"
            >Ganti outlet</button>
        </div>
        @else
        {{-- ── Search & list customer ────────────────────────────── --}}
        <div class="mb-2">
            <input
                type="search"
                class="search-input"
                placeholder="🔍 Cari nama outlet..."
                wire:model.live.debounce.300ms="search"
            >
        </div>

        <div class="falaya-card mb-3" style="overflow:hidden">
            @forelse ($customers as $customer)
            <div
                class="customer-row"
                wire:click="selectCustomer({{ $customer->id }})"
            >
                <div class="flex-grow-1 overflow-hidden">
                    <div style="font-size:0.92rem;font-weight:600;color:#1a1a2e">{{ $customer->customer_name }}</div>
                    <div style="font-size:0.78rem;color:#616876">{{ $customer->area->area_name ?? '-' }}</div>
                    <div style="font-size:0.75rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $customer->address }}</div>
                </div>
                <span style="color:#c0c4cc;font-size:0.9rem">›</span>
            </div>
            @empty
            <div style="padding:32px;text-align:center;color:#9ca3af">
                <div style="font-size:1.5rem;margin-bottom:8px">🔍</div>
                <div style="font-size:0.85rem">Outlet tidak ditemukan.</div>
            </div>
            @endforelse
        </div>
        @endif
    </div>

    @if ($selectedCustomer)
    <div class="confirm-bar">
        <button
            wire:click="confirm"
            wire:loading.attr="disabled"
            class="btn-submit"
        >
            ✓ Konfirmasi Kunjungan
        </button>
    </div>
    <div style="height:80px"></div>
    @endif
</div>
