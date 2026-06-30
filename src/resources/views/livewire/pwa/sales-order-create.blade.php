<div>
    {{-- ============================================================
         PWA Sales Order Create — Falaya SFA
         Livewire native penuh (2-step + payment toggle).
         Tidak ada state Alpine yang menduplikasi data Livewire —
         step & payment_type sepenuhnya dikontrol server.
         ============================================================ --}}
    <style>
        .falaya-card { border-radius: 12px; border: 1px solid #e6e7e9; margin-bottom: 12px; background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .falaya-card--danger  { border-color: #d63939; background: #fff5f5; }
        .falaya-card--warning { border-color: #f59f00; background: #fffbf0; }
        .falaya-card--info    { border-color: #206bc4; background: #f0f6ff; }
        .falaya-card--success { border-color: #2fb344; background: #f4fdf5; }
        .falaya-card__title   { font-weight: 600; font-size: 1rem; color: #1a1a2e; }
        /* Payment type toggle pill */
        .payment-toggle { display: flex; background: #f4f6fb; border-radius: 10px; padding: 4px; gap: 4px; }
        .payment-toggle__btn { flex: 1; min-height: 44px; border: none; border-radius: 8px; font-weight: 600; font-size: 0.92rem; transition: all 0.15s; background: transparent; color: #616876; }
        .payment-toggle__btn--active-cash   { background: #2fb344; color: white; box-shadow: 0 2px 6px rgba(47,179,68,0.3); }
        .payment-toggle__btn--active-credit { background: #206bc4; color: white; box-shadow: 0 2px 6px rgba(32,107,196,0.3); }
        /* Product row */
        .product-row { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-bottom: 1px solid #f0f0f0; }
        .product-row:last-child { border-bottom: none; }
        /* Qty stepper */
        .qty-stepper { display: flex; align-items: center; gap: 0; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
        .qty-btn { width: 36px; height: 36px; border: none; background: #f4f6fb; font-size: 1.1rem; font-weight: 600; color: #374151; display: flex; align-items: center; justify-content: center; }
        .qty-btn:active { background: #e6e7e9; }
        .qty-val { width: 40px; height: 36px; border: none; border-left: 1px solid #d1d5db; border-right: 1px solid #d1d5db; text-align: center; font-weight: 700; font-size: 0.95rem; color: #1a1a2e; background: white; }
        /* Sticky bottom bar */
        .sticky-bottom-bar { position: fixed; bottom: var(--pwa-navbar-h, 64px); left: 0; right: 0; background: white; border-top: 1px solid #e6e7e9; padding: 12px 16px; z-index: 110; box-shadow: 0 -2px 8px rgba(0,0,0,0.08); }
        .btn-submit { width: 100%; min-height: 50px; border-radius: 10px; border: none; background: #206bc4; color: white; font-weight: 700; font-size: 1rem; }
        .btn-submit:disabled { background: #9ca3af; }
        /* Step indicator */
        .step-indicator { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 16px; }
        .step-dot { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 700; }
        .step-dot--active { background: #206bc4; color: white; }
        .step-dot--done   { background: #2fb344; color: white; }
        .step-dot--idle   { background: #e6e7e9; color: #9ca3af; }
        .step-line { width: 40px; height: 2px; background: #e6e7e9; }
        .step-line--done { background: #2fb344; }
        /* Search input */
        .search-input { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 0.9rem; color: #374151; }
        .search-input:focus { outline: none; border-color: #206bc4; box-shadow: 0 0 0 3px rgba(32,107,196,0.12); }
        .credit-bar { height: 8px; border-radius: 4px; background: #e6e7e9; overflow: hidden; margin-top: 4px; }
        .credit-bar__fill { height: 100%; border-radius: 4px; transition: width 0.3s; }
        .back-btn { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #616876; text-decoration: none; padding: 12px 16px 4px; }
    </style>
    <a href="{{ route('pwa.pages.visits.detail', $visitPlan->id) }}" class="back-btn">‹ Detail Kunjungan</a>
    <div class="px-3 pt-1">
        {{-- ── Step Indicator (Livewire native) ────────────────────── --}}
        <div class="step-indicator">
            <div class="step-dot {{ $step >= 1 ? 'step-dot--active' : 'step-dot--idle' }}">1</div>
            <div class="step-line {{ $step >= 2 ? 'step-line--done' : '' }}"></div>
            <div class="step-dot {{ $step === 2 ? 'step-dot--active' : 'step-dot--idle' }}">2</div>
        </div>
        <div class="text-center mb-3" style="font-size:0.8rem;color:#616876">
            {{ $step === 1 ? 'Pilih Produk' : 'Konfirmasi Order' }}
        </div>

        @if ($submitError)
        <div class="falaya-card falaya-card--danger p-3 mb-3">
            <div style="font-size:0.85rem;color:#991b1b">⚠️ {{ $submitError }}</div>
        </div>
        @endif

        {{-- ══════════════ STEP 1: Pilih Produk ════════════════════ --}}
        @if ($step === 1)
        <div>
            {{-- Payment type toggle (Livewire native) --}}
            <div class="mb-3">
                <div style="font-size:0.75rem;font-weight:600;color:#616876;margin-bottom:8px">JENIS PEMBAYARAN</div>
                <div class="payment-toggle">
                    <button
                        type="button"
                        wire:click="setPaymentType('CASH')"
                        class="payment-toggle__btn {{ $paymentType === 'CASH' ? 'payment-toggle__btn--active-cash' : '' }}"
                    >
                        💵 CASH
                    </button>
                    @if ($customer->customer_type === 'CREDIT' && $customer->status === 'ACTIVE')
                    <button
                        type="button"
                        wire:click="setPaymentType('CREDIT')"
                        class="payment-toggle__btn {{ $paymentType === 'CREDIT' ? 'payment-toggle__btn--active-credit' : '' }}"
                    >
                        💳 KREDIT
                    </button>
                    @else
                    <button type="button" class="payment-toggle__btn" disabled style="opacity:0.4;cursor:not-allowed">
                        💳 KREDIT
                    </button>
                    @endif
                </div>
            </div>
            {{-- Credit usage info (only when CREDIT selected) --}}
            @if ($customer->customer_type === 'CREDIT' && $customer->credit_limit && $paymentType === 'CREDIT')
            @php
                $used = $customerOutstanding ?? 0;
                $limit = $customer->credit_limit;
                $orderTotal = collect($items)->sum(fn($i) => $i['qty'] * $i['unit_price']);
                $remaining = max(0, $limit - $used);
                $usedPct = $limit > 0 ? min(100, round(($used + $orderTotal) / $limit * 100)) : 0;
                $overLimit = ($used + $orderTotal) > $limit;
            @endphp
            <div class="mb-3">
                <div class="falaya-card {{ $overLimit ? 'falaya-card--danger' : 'falaya-card--info' }} p-3">
                    <div style="font-size:0.75rem;font-weight:600;color:#6b7280;margin-bottom:6px">LIMIT KREDIT</div>
                    <div class="d-flex justify-content-between" style="font-size:0.82rem;color:#374151;margin-bottom:4px">
                        <span>Limit: <strong>Rp {{ number_format($limit, 0, ',', '.') }}</strong></span>
                        <span>Sisa: <strong style="color:{{ $remaining > 0 ? '#15803d' : '#d63939' }}">Rp {{ number_format($remaining, 0, ',', '.') }}</strong></span>
                    </div>
                    <div class="credit-bar">
                        <div class="credit-bar__fill" style="width:{{ $usedPct }}%;background:{{ $overLimit ? '#d63939' : '#206bc4' }}"></div>
                    </div>
                    @if ($overLimit)
                    <div style="font-size:0.78rem;color:#991b1b;margin-top:6px;font-weight:600">
                        ⚠️ Order melebihi limit. Perlu persetujuan Owner.
                    </div>
                    @endif
                </div>
            </div>
            @endif
            {{-- Product search --}}
            <div class="mb-2">
                <input
                    type="search"
                    class="search-input"
                    placeholder="🔍 Cari produk..."
                    wire:model.live.debounce.300ms="search"
                >
            </div>
            {{-- Product list --}}
            <div class="falaya-card mb-3" style="overflow:hidden">
                @forelse ($availableProducts as $product)
                @php $orderItem = collect($items)->firstWhere('product_id', $product->id); $qty = $orderItem ? $orderItem['qty'] : 0; @endphp
                <div class="product-row">
                    <div class="flex-grow-1 overflow-hidden">
                        <div style="font-size:0.9rem;font-weight:600;color:#1a1a2e">{{ $product->product_name }}</div>
                        @if ($product->variant)
                        <div style="font-size:0.75rem;color:#616876">{{ $product->variant }}</div>
                        @endif
                        <div style="font-size:0.78rem;color:#9ca3af">Rp {{ number_format($product->selling_price, 0, ',', '.') }} / pcs</div>
                    </div>
                    @php $stock = $stockItems->firstWhere('product_id', $product->id); $stockQty = $stock ? $stock['qty'] : 0; @endphp
                    <div style="font-size:0.72rem;color:{{ $stockQty <= 5 ? '#d63939' : '#9ca3af' }};text-align:right;margin-right:4px;min-width:50px">
                        <div>Stok</div>
                        <div style="font-weight:600">{{ $stockQty }}</div>
                    </div>
                    <div class="qty-stepper">
                        <button
                            class="qty-btn"
                            wire:click="decrementQty({{ $product->id }})"
                            {{ $qty <= 0 ? 'disabled' : '' }}
                            style="{{ $qty <= 0 ? 'opacity:0.3' : '' }}"
                        >−</button>
                        <input
                            type="number"
                            class="qty-val"
                            value="{{ $qty }}"
                            wire:change="setQty({{ $product->id }}, $event.target.value)"
                            min="0"
                            max="{{ $stockQty }}"
                        >
                        <button
                            class="qty-btn"
                            wire:click="incrementQty({{ $product->id }})"
                            {{ $qty >= $stockQty ? 'disabled' : '' }}
                            style="{{ $qty >= $stockQty ? 'opacity:0.3' : '' }}"
                        >＋</button>
                    </div>
                </div>
                @empty
                <div style="padding:32px;text-align:center;color:#9ca3af">
                    <div style="font-size:1.5rem;margin-bottom:8px">📦</div>
                    <div style="font-size:0.85rem">Tidak ada produk ditemukan</div>
                </div>
                @endforelse
            </div>
        </div>
        @endif{{-- end step 1 --}}
        {{-- ══════════════ STEP 2: Konfirmasi ══════════════════════ --}}
        @if ($step === 2)
        <div>
            <div class="falaya-card p-3 mb-3">
                <div class="falaya-card__title mb-2">📋 Ringkasan Pesanan</div>
                <div style="font-size:0.82rem;color:#6b7280;margin-bottom:8px">
                    🏪 {{ $customer->customer_name }} · {{ $customer->area->area_name }}
                </div>
                <div style="font-size:0.82rem;margin-bottom:12px">
                    <span style="background:{{ $paymentType === 'CASH' ? '#dcfce7' : '#dbeafe' }};color:{{ $paymentType === 'CASH' ? '#15803d' : '#1d4ed8' }};padding:2px 10px;border-radius:20px;font-weight:600">
                        {{ $paymentType === 'CASH' ? '💵 CASH' : '💳 KREDIT' }}
                    </span>
                </div>
                @foreach (collect($items)->filter(fn($i) => $i['qty'] > 0) as $item)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <div style="font-size:0.9rem;font-weight:600;color:#374151">{{ $item['product_name'] }}</div>
                        <div style="font-size:0.78rem;color:#9ca3af">{{ $item['qty'] }} × Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</div>
                    </div>
                    <div style="font-weight:700;color:#1a1a2e">Rp {{ number_format($item['qty'] * $item['unit_price'], 0, ',', '.') }}</div>
                </div>
                @endforeach
                <div class="d-flex justify-content-between align-items-center pt-3">
                    <div style="font-weight:700;color:#1a1a2e;font-size:1rem">Total</div>
                    <div style="font-size:1.25rem;font-weight:800;color:#206bc4">
                        Rp {{ number_format(collect($items)->filter(fn($i) => $i['qty'] > 0)->sum(fn($i) => $i['qty'] * $i['unit_price']), 0, ',', '.') }}
                    </div>
                </div>
            </div>
            @if ($paymentType === 'CREDIT' && $overLimit)
            <div class="falaya-card falaya-card--danger p-3 mb-3">
                <div class="d-flex gap-2">
                    <span style="font-size:1.2rem">❌</span>
                    <div>
                        <div style="font-weight:600;color:#991b1b">Melebihi Limit Kredit</div>
                        <div style="font-size:0.85rem;color:#7f1d1d;margin-top:4px">
                            Order ini Rp {{ number_format($orderTotal ?? 0, 0, ',', '.') }}, sisa limit hanya Rp {{ number_format($remaining ?? 0, 0, ',', '.') }}.
                        </div>
                        <div style="font-size:0.82rem;color:#7f1d1d;margin-top:4px">Tap "Kirim Order" untuk mengajukan persetujuan ke Owner.</div>
                    </div>
                </div>
            </div>
            @endif
            <button
                wire:click="prevStep"
                style="width:100%;min-height:44px;border-radius:10px;border:1px solid #d1d5db;background:white;color:#374151;font-weight:600;margin-bottom:12px"
            >‹ Ubah Pesanan</button>
        </div>
        @endif{{-- end step 2 --}}
    {{-- ── Sticky Bottom Bar ──────────────────────────────────────── --}}
    <div class="sticky-bottom-bar">
        @php $grandTotal = collect($items)->filter(fn($i) => $i['qty'] > 0)->sum(fn($i) => $i['qty'] * $i['unit_price']); @endphp
        @if ($step === 1)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div style="font-size:0.82rem;color:#616876">Total</div>
            <div style="font-size:1.1rem;font-weight:800;color:#206bc4">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
        </div>
        <button wire:click="nextStep" class="btn-submit">
            Lanjut → Konfirmasi
        </button>
        @endif
        @if ($step === 2)
        <button wire:click="submitOrder" class="btn-submit">
            🛒 Kirim Order
        </button>
        @endif
    </div>{{-- end sticky-bottom-bar --}}
    <div style="height:20px"></div>
    </div>
</div>
