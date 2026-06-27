<div>
    {{-- ============================================================
         PWA Collection Create — Falaya SFA
         Livewire + Alpine.js for invoice selection & skip flow
         ============================================================ --}}

    <style>
        .falaya-card { border-radius: 12px; border: 1px solid #e6e7e9; margin-bottom: 12px; background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .falaya-card--danger  { border-color: #d63939; background: #fff5f5; }
        .falaya-card--warning { border-color: #f59f00; background: #fffbf0; }
        .falaya-card--success { border-color: #2fb344; background: #f4fdf5; }
        .falaya-card--info    { border-color: #206bc4; background: #f0f6ff; }
        .falaya-card__title   { font-weight: 600; font-size: 1rem; color: #1a1a2e; }
        /* Invoice card */
        .invoice-card { border-radius: 10px; border: 2px solid #e6e7e9; margin-bottom: 8px; overflow: hidden; transition: border-color 0.15s; cursor: pointer; }
        .invoice-card--selected { border-color: #206bc4; background: #f0f6ff; }
        .invoice-card--overdue  { border-color: #d63939; }
        .invoice-card--duesoon  { border-color: #f59f00; }
        .invoice-card__body { display: flex; align-items: center; gap: 12px; padding: 12px 14px; min-height: 60px; }
        .invoice-check { width: 24px; height: 24px; border-radius: 50%; border: 2px solid #d1d5db; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; }
        .invoice-check--checked { background: #206bc4; border-color: #206bc4; }
        /* Amount input */
        .amount-input { width: 100%; font-size: 1.5rem; font-weight: 700; color: #1a1a2e; border: none; border-bottom: 2px solid #206bc4; padding: 8px 0; text-align: center; background: transparent; }
        .amount-input:focus { outline: none; }
        /* Sticky bottom */
        .sticky-bottom-bar { position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e6e7e9; padding: 12px 16px calc(12px + env(safe-area-inset-bottom)); z-index: 50; box-shadow: 0 -2px 8px rgba(0,0,0,0.08); }
        .btn-submit { width: 100%; min-height: 50px; border-radius: 10px; border: none; background: #206bc4; color: white; font-weight: 700; font-size: 1rem; }
        .btn-submit:disabled { background: #9ca3af; }
        /* Skip section */
        .skip-toggle { display: flex; align-items: center; justify-content: space-between; padding: 14px; cursor: pointer; user-select: none; }
        .skip-toggle-label { font-size: 0.88rem; color: #616876; font-weight: 600; }
        .skip-radio { display: flex; align-items: center; gap: 10px; padding: 12px 14px; min-height: 44px; border-top: 1px solid #f0f0f0; cursor: pointer; }
        .skip-radio:first-of-type { border-top: none; }
        .skip-radio input[type=radio] { accent-color: #d63939; width: 18px; height: 18px; flex-shrink: 0; }
        .skip-radio label { font-size: 0.88rem; color: #374151; cursor: pointer; flex-grow: 1; }
        .back-btn { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #616876; text-decoration: none; padding: 12px 16px 4px; }
        .select-all-btn { font-size: 0.8rem; color: #206bc4; font-weight: 600; text-decoration: none; background: none; border: none; padding: 0; }
    </style>

    <a href="{{ route('pwa.visits.show', $visitPlan->id) }}" class="back-btn">‹ Detail Kunjungan</a>

    <div
        class="px-3 pt-1"
        x-data="{
            selected: [],
            manualAmount: null,
            skipOpen: false,
            skipReason: '',

            get selectedTotal() {
                const invoices = {{ json_encode($invoices) }};
                return invoices
                    .filter(i => this.selected.includes(i.id))
                    .reduce((sum, i) => sum + i.remaining_amount, 0);
            },

            get displayAmount() {
                return this.manualAmount !== null ? this.manualAmount : this.selectedTotal;
            },

            formatRp(n) {
                return 'Rp ' + Math.round(n).toLocaleString('id-ID');
            },

            toggleInvoice(id) {
                const idx = this.selected.indexOf(id);
                if (idx >= 0) { this.selected.splice(idx, 1); }
                else { this.selected.push(id); }
                this.manualAmount = null; {{-- reset manual amount on selection change --}}
            },

            selectAll() {
                const invoices = {{ json_encode($invoices) }};
                this.selected = invoices.map(i => i.id);
                this.manualAmount = null;
            },

            clearAll() {
                this.selected = [];
                this.manualAmount = null;
            }
        }"
    >

        {{-- ── Outstanding summary card ─────────────────────────── --}}
        <div class="falaya-card p-3 mb-3">
            <div style="font-size:0.75rem;font-weight:600;color:#6b7280;margin-bottom:4px">💰 TOTAL TAGIHAN</div>
            <div style="font-size:1.75rem;font-weight:800;color:#1a1a2e;line-height:1.2">
                Rp {{ number_format($invoices->sum('remaining_amount'), 0, ',', '.') }}
            </div>
            <div style="font-size:0.82rem;color:#616876;margin-top:4px">
                {{ $invoices->count() }} invoice · {{ $customer->customer_name }}
            </div>
        </div>

        {{-- ── Invoice list ─────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div style="font-size:0.75rem;font-weight:600;color:#616876;text-transform:uppercase;letter-spacing:0.04em">Pilih Invoice</div>
            <div class="d-flex gap-3">
                <button class="select-all-btn" @click="selectAll()">Pilih Semua</button>
                <button class="select-all-btn" style="color:#d63939" @click="clearAll()" x-show="selected.length > 0">Hapus</button>
            </div>
        </div>

        @forelse ($invoices as $invoice)
        @php
            $isOverdue = $invoice->is_overdue ?? false;
            $isDueSoon = $invoice->is_due_soon ?? false;
        @endphp
        <div
            class="invoice-card {{ $isOverdue ? 'invoice-card--overdue' : ($isDueSoon ? 'invoice-card--duesoon' : '') }}"
            :class="selected.includes({{ $invoice->id }}) ? 'invoice-card--selected' : ''"
            @click="toggleInvoice({{ $invoice->id }})"
        >
            {{-- Overdue / Due soon banner strip --}}
            @if ($isOverdue)
            <div style="background:#d63939;color:white;font-size:0.7rem;font-weight:700;padding:3px 12px">🔴 TERLAMBAT {{ $invoice->days_overdue }} HARI</div>
            @elseif ($isDueSoon)
            <div style="background:#f59f00;color:white;font-size:0.7rem;font-weight:700;padding:3px 12px">⏰ JATUH TEMPO {{ $invoice->days_to_due }} HARI LAGI</div>
            @endif

            <div class="invoice-card__body">
                {{-- Checkbox visual --}}
                <div class="invoice-check" :class="selected.includes({{ $invoice->id }}) ? 'invoice-check--checked' : ''">
                    <span style="color:white;font-size:0.8rem;font-weight:700" x-show="selected.includes({{ $invoice->id }})">✓</span>
                </div>

                {{-- Invoice info --}}
                <div class="flex-grow-1">
                    <div style="font-size:0.88rem;font-weight:600;color:#1a1a2e">{{ $invoice->invoice_number }}</div>
                    <div style="font-size:0.75rem;color:#9ca3af">
                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
                        · Tempo: {{ $invoice->credit_term_days_snapshot }}h
                    </div>
                </div>

                {{-- Amount --}}
                <div style="text-align:right">
                    <div style="font-size:0.95rem;font-weight:700;color:#1a1a2e">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</div>
                    @if ($invoice->paid_amount > 0)
                    <div style="font-size:0.7rem;color:#9ca3af">Bayar: Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:32px;color:#9ca3af">
            <div style="font-size:2rem;margin-bottom:8px">✅</div>
            <div style="font-size:0.85rem">Tidak ada tagihan outstanding</div>
        </div>
        @endforelse

        {{-- ── Payment input section (shows when selection made) ── --}}
        <div x-show="selected.length > 0" x-transition class="mb-3">
            <div class="falaya-card p-3">
                <div style="font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px">JUMLAH DITERIMA</div>

                <div style="position:relative">
                    <div style="font-size:0.85rem;color:#9ca3af;position:absolute;left:0;top:10px">Rp</div>
                    <input
                        type="number"
                        class="amount-input"
                        style="padding-left:28px"
                        :value="displayAmount"
                        @input="manualAmount = Number($event.target.value)"
                        wire:model="amountReceived"
                        min="0"
                        inputmode="numeric"
                    >
                </div>

                <div style="font-size:0.75rem;color:#9ca3af;text-align:center;margin-top:6px">
                    Alokasi otomatis FIFO · <span x-text="selected.length"></span> invoice dipilih
                </div>

                {{-- Selected total --}}
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <div style="font-size:0.82rem;color:#616876">Total tagihan dipilih</div>
                    <div style="font-weight:700;color:#206bc4" x-text="formatRp(selectedTotal)"></div>
                </div>
            </div>

            {{-- Surplus / shortfall indicator --}}
            <div
                x-show="manualAmount !== null && manualAmount !== selectedTotal"
                x-transition
                class="falaya-card p-3"
                :class="manualAmount > selectedTotal ? 'falaya-card--warning' : 'falaya-card--danger'"
            >
                <div style="font-size:0.85rem;font-weight:600" :style="manualAmount > selectedTotal ? 'color:#92400e' : 'color:#991b1b'">
                    <span x-show="manualAmount > selectedTotal">⚠️ Lebih Rp <span x-text="formatRp(manualAmount - selectedTotal).replace('Rp ','')"></span> — kelebihan dicatat sebagai uang muka</span>
                    <span x-show="manualAmount < selectedTotal">❌ Kurang Rp <span x-text="formatRp(selectedTotal - manualAmount).replace('Rp ','')"></span> — pembayaran parsial</span>
                </div>
            </div>
        </div>

        {{-- ── Skip / Lewati section (collapsible) ──────────────── --}}
        <div class="falaya-card mb-3" style="overflow:hidden" x-data="{ skipOpen: false, skipReason: '', otherNote: '' }">
            <div class="skip-toggle" @click="skipOpen = !skipOpen">
                <span class="skip-toggle-label">❌ Lewati Penagihan</span>
                <span style="font-size:0.85rem;color:#9ca3af;transition:transform 0.2s" :style="skipOpen ? 'transform:rotate(180deg)' : ''">▾</span>
            </div>

            <div x-show="skipOpen" x-collapse>
                <div class="skip-radio">
                    <input type="radio" id="reason1" name="skip_reason" value="no_money" wire:model="skipReason" x-model="skipReason">
                    <label for="reason1">Tidak ada uang</label>
                </div>
                <div class="skip-radio">
                    <input type="radio" id="reason2" name="skip_reason" value="reschedule" wire:model="skipReason" x-model="skipReason">
                    <label for="reason2">Minta reschedule</label>
                </div>
                <div class="skip-radio">
                    <input type="radio" id="reason3" name="skip_reason" value="already_transfer" wire:model="skipReason" x-model="skipReason">
                    <label for="reason3">Sudah transfer sendiri</label>
                </div>
                <div class="skip-radio">
                    <input type="radio" id="reason4" name="skip_reason" value="other" wire:model="skipReason" x-model="skipReason">
                    <label for="reason4">Lainnya</label>
                </div>

                <div x-show="skipReason === 'other'" class="px-3 pb-3">
                    <textarea
                        wire:model="skipNote"
                        placeholder="Tulis catatan..."
                        class="form-control"
                        rows="2"
                        style="border-radius:8px;font-size:0.88rem"
                    ></textarea>
                </div>

                <div class="px-3 pb-3">
                    <button
                        wire:click="skipCollection"
                        class="w-100"
                        style="min-height:44px;border-radius:8px;border:1.5px solid #d63939;background:white;color:#d63939;font-weight:600;font-size:0.9rem"
                        :disabled="!skipReason"
                        :style="!skipReason ? 'opacity:0.5' : ''"
                    >
                        Lewati Penagihan
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Sticky Bottom Bar ────────────────────────────────────── --}}
    <div class="sticky-bottom-bar">
        <div class="d-flex justify-content-between align-items-center mb-2" x-show="selected.length > 0">
            <div style="font-size:0.82rem;color:#616876">Akan dibayar</div>
            <div style="font-size:1.1rem;font-weight:800;color:#206bc4" x-text="formatRp(displayAmount)"></div>
        </div>
        <button
            wire:click="savePayment"
            class="btn-submit"
            x-bind:disabled="selected.length === 0 || displayAmount <= 0"
            :style="selected.length === 0 || displayAmount <= 0 ? 'background:#9ca3af' : ''"
        >
            <span x-show="selected.length === 0">Pilih invoice untuk melanjutkan</span>
            <span x-show="selected.length > 0">💰 Simpan Pembayaran</span>
        </button>
    </div>

    <div style="height:100px"></div>
</div>
