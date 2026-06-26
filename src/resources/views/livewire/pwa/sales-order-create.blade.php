<div
    x-data="{
        submitting: false,
        submitError: null,
        submitSuccess: null,
        csrfToken: '{{ csrf_token() }}',

        async submitOrder() {
            this.submitting = true;
            this.submitError = null;

            const items = @json($items).filter(i => i.qty > 0);

            if (items.length === 0) {
                this.submitError = 'Pilih minimal 1 produk.';
                this.submitting = false;
                return;
            }

            const payload = {
                visit_plan_id: {{ $visitPlan->id }},
                customer_id: {{ $visitPlan->customer_id }},
                payment_type: document.getElementById('payment_type').value,
                receiver_name: document.getElementById('receiver_name')?.value ?? null,
                items: items.map(i => ({
                    product_id: i.product_id,
                    qty: i.qty,
                    unit_price: i.unit_price,
                })),
            };

            try {
                // Step 1: Create DRAFT
                const createRes = await fetch('/pwa/api/sales-orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const createData = await createRes.json();
                if (!createRes.ok) {
                    this.submitError = createData.message ?? 'Gagal membuat order.';
                    this.submitting = false;
                    return;
                }

                // Step 2: POST
                const postRes = await fetch('/pwa/api/sales-orders/' + createData.sales_order_id + '/post', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });

                const postData = await postRes.json();

                if (postRes.ok) {
                    this.submitSuccess = 'Order ' + postData.document_number + ' berhasil! Total: Rp {{ number_format(0) }}';
                    setTimeout(() => { window.location.href = '/pwa/visits/{{ $visitPlan->id }}'; }, 2000);
                } else if (postData.requires_override) {
                    this.submitError = 'Melebihi limit kredit. Permintaan override dikirim ke Owner.';
                } else {
                    this.submitError = postData.message ?? 'Gagal memposting order.';
                }

            } catch (e) {
                this.submitError = 'Koneksi bermasalah. Coba lagi.';
            }

            this.submitting = false;
        },
    }"
>
    <div class="pwa-header">
        <a href="/pwa/visits/{{ $visitPlan->id }}" class="text-white text-decoration-none opacity-75">← Kembali</a>
        <h5 class="mt-1">Pesanan Baru</h5>
        <small>{{ $visitPlan->customer->customer_name }}</small>
    </div>

    <div class="px-3">

        {{-- Success --}}
        <div x-show="submitSuccess" x-cloak class="falaya-card falaya-card--success">
            <div class="card-body text-center">
                <div class="falaya-card__title">✅ <span x-text="submitSuccess"></span></div>
            </div>
        </div>

        {{-- Error --}}
        <div x-show="submitError" x-cloak class="falaya-card falaya-card--danger">
            <div class="card-body">
                <div class="falaya-card__title">❌ <span x-text="submitError"></span></div>
            </div>
        </div>

        {{-- Payment type --}}
        <div class="falaya-card">
            <div class="card-body">
                <div class="falaya-card__title mb-2">Jenis Pembayaran</div>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_type_radio"
                            id="payment_type" value="CASH" checked
                            onchange="document.getElementById('payment_type').value=this.value;document.getElementById('credit_fields').style.display='none'">
                        <label class="form-check-label" for="pt_cash">Cash</label>
                    </div>
                    @if($visitPlan->customer->customer_type === 'CREDIT')
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_type_radio"
                            value="CREDIT"
                            onchange="document.getElementById('payment_type').value=this.value;document.getElementById('credit_fields').style.display='block'">
                        <label class="form-check-label">Kredit</label>
                    </div>
                    @endif
                </div>
                <input type="hidden" id="payment_type" value="CASH">

                <div id="credit_fields" style="display:none" class="mt-3">
                    <label class="form-label">Nama Penerima Barang</label>
                    <input type="text" class="form-control" id="receiver_name" placeholder="Pak Budi">
                    @if($visitPlan->customer->credit_limit)
                        <small class="text-muted d-block mt-1">
                            Limit: Rp {{ number_format($visitPlan->customer->credit_limit, 0, ',', '.') }}
                        </small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Produk --}}
        <div class="falaya-card">
            <div class="card-body">
                <div class="falaya-card__title mb-3">Produk</div>

                @forelse($items as $index => $item)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
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
            <button class="btn btn-primary btn-lg"
                :disabled="submitting"
                @click="submitOrder()">
                <span x-show="submitting">⏳ Memproses...</span>
                <span x-show="!submitting">🛒 Kirim Pesanan</span>
            </button>
        </div>

    </div>
</div>
