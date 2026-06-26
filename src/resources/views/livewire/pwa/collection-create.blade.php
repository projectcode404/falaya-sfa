<div
    x-data="{
        submitting: false,
        submitError: null,
        submitSuccess: null,
        skipMode: false,
        csrfToken: '{{ csrf_token() }}',

        async submitPayment() {
            this.submitting = true;
            this.submitError = null;

            const selected = @json($selected);
            const amounts = @json($amounts);
            const invoices = @json($invoices);

            const allocations = invoices
                .filter(inv => selected[inv.id])
                .map(inv => ({
                    invoice_id: inv.id,
                    amount: parseFloat(amounts[inv.id]) || 0,
                }))
                .filter(a => a.amount > 0);

            if (allocations.length === 0) {
                this.submitError = 'Pilih minimal 1 invoice.';
                this.submitting = false;
                return;
            }

            const total = allocations.reduce((s, a) => s + a.amount, 0);

            try {
                const res = await fetch('/pwa/api/collection/payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        customer_id: {{ $visitPlan->customer_id }},
                        visit_plan_id: {{ $visitPlan->id }},
                        total_amount: total,
                        notes: document.getElementById('payment_notes').value,
                        allocations: allocations,
                    }),
                });

                const data = await res.json();

                if (res.ok) {
                    this.submitSuccess = 'Pembayaran ' + data.payment_number + ' berhasil dicatat!';
                    setTimeout(() => { window.location.href = '/pwa/visits/{{ $visitPlan->id }}'; }, 2000);
                } else {
                    this.submitError = data.message ?? 'Gagal mencatat pembayaran.';
                }
            } catch (e) {
                this.submitError = 'Koneksi bermasalah. Coba lagi.';
            }

            this.submitting = false;
        },

        async submitSkip(reason) {
            this.submitting = true;
            this.submitError = null;

            try {
                const taskId = {{ $visitPlan->collectionTask?->id ?? 'null' }};
                if (!taskId) {
                    window.location.href = '/pwa/visits/{{ $visitPlan->id }}';
                    return;
                }

                const res = await fetch('/pwa/api/collection/skip', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        collection_task_id: taskId,
                        reason: reason,
                        notes: document.getElementById('skip_notes')?.value ?? '',
                    }),
                });

                const data = await res.json();
                if (res.ok) {
                    this.submitSuccess = 'Penagihan dilewati.';
                    setTimeout(() => { window.location.href = '/pwa/visits/{{ $visitPlan->id }}'; }, 1500);
                } else {
                    this.submitError = data.message ?? 'Gagal.';
                }
            } catch (e) {
                this.submitError = 'Koneksi bermasalah.';
            }

            this.submitting = false;
        },
    }"
>
    <div class="pwa-header">
        <a href="/pwa/visits/{{ $visitPlan->id }}" class="text-white text-decoration-none opacity-75">← Kembali</a>
        <h5 class="mt-1">Catat Pembayaran</h5>
        <small>{{ $visitPlan->customer->customer_name }}</small>
    </div>

    <div class="px-3">

        <div x-show="submitSuccess" x-cloak class="falaya-card falaya-card--success">
            <div class="card-body text-center">
                <div class="falaya-card__title">✅ <span x-text="submitSuccess"></span></div>
            </div>
        </div>

        <div x-show="submitError" x-cloak class="falaya-card falaya-card--danger">
            <div class="card-body">
                <div class="falaya-card__title">❌ <span x-text="submitError"></span></div>
            </div>
        </div>

        @if(count($invoices) === 0)
            <div class="falaya-card">
                <div class="card-body text-center py-4">
                    <div style="font-size:2rem">✅</div>
                    <div class="falaya-card__subtitle mt-2">Tidak ada tagihan outstanding untuk outlet ini.</div>
                    <a href="/pwa/visits/{{ $visitPlan->id }}" class="btn btn-outline-primary btn-sm mt-3">← Kembali</a>
                </div>
            </div>
        @else

            {{-- Mode pembayaran --}}
            <div x-show="!skipMode">

                {{-- Invoice list --}}
                <div class="falaya-card">
                    <div class="card-body">
                        <div class="falaya-card__title mb-3">⚠️ Outstanding</div>
                        @foreach($invoices as $inv)
                            <div class="py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            wire:click="toggleSelect({{ $inv['id'] }})"
                                            {{ ($selected[$inv['id']] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            <div class="falaya-card__title" style="font-size:0.875rem">{{ $inv['invoice_number'] }}</div>
                                            <small class="text-muted">Jatuh tempo: {{ $inv['due_date'] }}</small>
                                        </label>
                                    </div>
                                    <span class="badge {{ $inv['status'] === 'OVERDUE' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                        {{ $inv['status'] }}
                                    </span>
                                </div>
                                @if($selected[$inv['id']] ?? false)
                                    <div class="mt-2">
                                        <label class="form-label small">Jumlah (maks Rp {{ number_format($inv['remaining_amount'], 0, ',', '.') }})</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control"
                                                wire:model.lazy="amounts.{{ $inv['id'] }}"
                                                min="0" max="{{ $inv['remaining_amount'] }}"
                                                step="1000">
                                        </div>
                                    </div>
                                @else
                                    <small class="text-muted d-block mt-1">Sisa: Rp {{ number_format($inv['remaining_amount'], 0, ',', '.') }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="falaya-card">
                    <div class="card-body">
                        <label class="form-label">Catatan (opsional)</label>
                        <input type="text" class="form-control" id="payment_notes" placeholder="Bayar tunai">
                    </div>
                </div>

                {{-- Total --}}
                <div class="falaya-card falaya-card--success">
                    <div class="card-body d-flex justify-content-between">
                        <div class="falaya-card__title">Total Diterima</div>
                        <div class="fw-bold fs-5">Rp {{ number_format($total, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button class="btn btn-primary btn-lg" :disabled="submitting" @click="submitPayment()">
                        <span x-show="submitting">⏳ Memproses...</span>
                        <span x-show="!submitting">💰 Simpan Pembayaran</span>
                    </button>
                    <button class="btn btn-outline-secondary" @click="skipMode = true">
                        Lewati Penagihan
                    </button>
                </div>

            </div>

            {{-- Mode lewati --}}
            <div x-show="skipMode" x-cloak>
                <div class="falaya-card">
                    <div class="card-body">
                        <div class="falaya-card__title mb-3">Alasan Melewati</div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary text-start" @click="submitSkip('no_money')">
                                💸 Tidak ada uang
                            </button>
                            <button class="btn btn-outline-secondary text-start" @click="submitSkip('reschedule')">
                                📅 Minta reschedule
                            </button>
                            <button class="btn btn-outline-secondary text-start" @click="submitSkip('already_transferred')">
                                🏦 Sudah transfer sendiri
                            </button>
                            <input type="text" class="form-control" id="skip_notes" placeholder="Alasan lain...">
                            <button class="btn btn-outline-danger" @click="submitSkip('other')">
                                Lewati dengan alasan di atas
                            </button>
                        </div>
                        <button class="btn btn-link mt-2" @click="skipMode = false">← Batal</button>
                    </div>
                </div>
            </div>

        @endif
    </div>
</div>
