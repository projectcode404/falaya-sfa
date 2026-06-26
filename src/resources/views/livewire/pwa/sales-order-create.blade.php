<div>
    <div class="pwa-header">
        <a href="/pwa/visits/{{ $visitPlan->id }}" class="text-white text-decoration-none opacity-75">← Kembali</a>
        <h5 class="mt-1">Pesanan Baru</h5>
        <small>{{ $visitPlan->customer->customer_name }}</small>
    </div>

    <div class="px-3">

        @if($submitSuccess)
            <div class="falaya-card falaya-card--success">
                <div class="card-body text-center">
                    <div class="falaya-card__title">✅ {{ $submitSuccess }}</div>
                </div>
            </div>
        @endif

        @if($submitError)
            <div class="falaya-card falaya-card--danger">
                <div class="card-body">
                    <div class="falaya-card__title">❌ {{ $submitError }}</div>
                </div>
            </div>
        @endif

        {{-- Payment type --}}
        <div class="falaya-card">
            <div class="card-body">
                <div class="falaya-card__title mb-2">Jenis Pembayaran</div>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" wire:model.live="payment_type" value="CASH" id="pt_cash">
                        <label class="form-check-label" for="pt_cash">Cash</label>
                    </div>
                    @if($visitPlan->customer->customer_type === 'CREDIT')
                    <div class="form-check">
                        <input class="form-check-input" type="radio" wire:model.live="payment_type" value="CREDIT" id="pt_credit">
                        <label class="form-check-label" for="pt_credit">Kredit</label>
                    </div>
                    @endif
                </div>

                @if($payment_type === 'CREDIT')
                <div class="mt-3">
                    <label class="form-label">Nama Penerima Barang</label>
                    <input type="text" class="form-control" wire:model="receiver_name" placeholder="Pak Budi">
                    @if($visitPlan->customer->credit_limit)
                        <small class="text-muted d-block mt-1">
                            Limit: Rp {{ number_format($visitPlan->customer->credit_limit, 0, ',', '.') }}
                        </small>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Produk --}}
        <div class="falaya-card">
            <div class="card-body">
                <div class="falaya-card__title mb-3">Produk</div>

                @forelse($items as $index => $item)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                        wire:key="item-{{ $item['product_id'] }}">
                        <div>
                            <div class="falaya-card__title" style="font-size:0.9rem">{{ $item['product_name'] }}</div>
                            <small class="text-muted">
                                Rp {{ number_format($item['unit_price'], 0, ',', '.') }} / {{ $item['unit'] }}
                                · Stok: {{ number_format($item['max_qty'], 0) }}
                            </small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary px-2 py-1"
                                wire:click="decrementQty({{ $index }})"
                                style="min-height:36px;min-width:36px">−</button>
                            <span class="fw-bold" style="min-width:24px;text-align:center">{{ $item['qty'] }}</span>
                            <button class="btn btn-sm btn-outline-primary px-2 py-1"
                                wire:click="incrementQty({{ $index }})"
                                style="min-height:36px;min-width:36px">+</button>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Tidak ada stok bawaan. Hubungi Admin untuk loading.</p>
                @endforelse
            </div>
        </div>

        {{-- Total --}}
        <div class="falaya-card falaya-card--success">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="falaya-card__title">Total</div>
                <div class="fw-bold fs-5">Rp {{ number_format($total, 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="d-grid mt-2 mb-4">
            <button class="btn btn-primary btn-lg" wire:click="submitOrder" wire:loading.attr="disabled" wire:target="submitOrder">
                <span wire:loading wire:target="submitOrder">⏳ Memproses...</span>
                <span wire:loading.remove wire:target="submitOrder">🛒 Kirim Pesanan</span>
            </button>
        </div>

    </div>
</div>
