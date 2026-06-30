<div>
    {{-- ============================================================
         PWA Collection Create — Falaya SFA
         Livewire native penuh. selected/amounts dikontrol server,
         tidak ada state Alpine yang menduplikasi data Livewire.
         ============================================================ --}}
    <style>
        .falaya-card { border-radius: 12px; border: 1px solid #e6e7e9; margin-bottom: 12px; background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .falaya-card--danger  { border-color: #d63939; background: #fff5f5; }
        .falaya-card--warning { border-color: #f59f00; background: #fffbf0; }
        .falaya-card--success { border-color: #2fb344; background: #f4fdf5; }
        .falaya-card--info    { border-color: #f59e0b; background: #f0f6ff; }
        .falaya-card__title   { font-weight: 600; font-size: 1rem; color: #1a1a2e; }
        /* Invoice card */
        .invoice-card { border-radius: 10px; border: 2px solid #e6e7e9; margin-bottom: 8px; overflow: hidden; transition: border-color 0.15s; cursor: pointer; }
        .invoice-card--selected { border-color: #f59e0b; background: #f0f6ff; }
        .invoice-card--overdue  { border-color: #d63939; }
        .invoice-card--duesoon  { border-color: #f59f00; }
        .invoice-card__body { display: flex; align-items: center; gap: 12px; padding: 12px 14px; min-height: 60px; }
        .invoice-check { width: 24px; height: 24px; border-radius: 50%; border: 2px solid #d1d5db; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; }
        .invoice-check--checked { background: #f59e0b; border-color: #f59e0b; }
        /* Amount input */
        .amount-input { width: 100%; font-size: 1.5rem; font-weight: 700; color: #1a1a2e; border: none; border-bottom: 2px solid #f59e0b; padding: 8px 0; text-align: center; background: transparent; }
        .amount-input:focus { outline: none; }
        /* Sticky bottom */
        .sticky-bottom-bar { position: fixed; bottom: var(--pwa-navbar-h, 64px); left: 0; right: 0; background: white; border-top: 1px solid #e6e7e9; padding: 12px 16px; z-index: 110; box-shadow: 0 -2px 8px rgba(0,0,0,0.08); }
        .btn-submit { width: 100%; min-height: 50px; border-radius: 10px; border: none; background: #f59e0b; color: #0f172a; font-weight: 700; font-size: 1rem; }
        .btn-submit:disabled { background: #9ca3af; }
        /* Skip section */
        .skip-toggle { display: flex; align-items: center; justify-content: space-between; padding: 14px; cursor: pointer; user-select: none; }
        .skip-toggle-label { font-size: 0.88rem; color: #616876; font-weight: 600; }
        .skip-radio { display: flex; align-items: center; gap: 10px; padding: 12px 14px; min-height: 44px; border-top: 1px solid #f0f0f0; cursor: pointer; }
        .skip-radio:first-of-type { border-top: none; }
        .skip-radio input[type=radio] { accent-color: #d63939; width: 18px; height: 18px; flex-shrink: 0; }
        .skip-radio label { font-size: 0.88rem; color: #374151; cursor: pointer; flex-grow: 1; }
        .select-all-btn { font-size: 0.8rem; color: #f59e0b; font-weight: 600; text-decoration: none; background: none; border: none; padding: 0; }
    </style>

    <a href="{{ route('pwa.pages.visits.detail', $visitPlan->id) }}" class="back-btn">‹ Detail Kunjungan</a>

    <div class="px-3 pt-1" x-data="{ skipOpen: false }">

        @if ($submitError)
        <div class="falaya-card falaya-card--danger p-3 mb-3">
            <div style="font-size:0.85rem;color:#991b1b">⚠️ {{ $submitError }}</div>
        </div>
        @endif

        {{-- ── Outstanding summary card ─────────────────────────── --}}
        <div class="falaya-card p-3 mb-3">
            <div style="font-size:0.75rem;font-weight:600;color:#6b7280;margin-bottom:4px">💰 TOTAL TAGIHAN</div>
            <div style="font-size:1.75rem;font-weight:800;color:#1a1a2e;line-height:1.2">
                Rp {{ number_format(array_sum(array_column($invoices, 'remaining_amount')), 0, ',', '.') }}
            </div>
            <div style="font-size:0.82rem;color:#616876;margin-top:4px">
                {{ count($invoices) }} invoice · {{ $customer->customer_name }}
            </div>
        </div>

        {{-- ── Invoice list ─────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div style="font-size:0.75rem;font-weight:600;color:#616876;text-transform:uppercase;letter-spacing:0.04em">Pilih Invoice</div>
            <div class="d-flex gap-3">
                <button type="button" class="select-all-btn" wire:click="selectAll">Pilih Semua</button>
                @if ($selectedCount > 0)
                <button type="button" class="select-all-btn" style="color:#d63939" wire:click="clearAll">Hapus</button>
                @endif
            </div>
        </div>

        @forelse ($invoices as $invoice)
        <div
            class="invoice-card {{ $invoice['is_overdue'] ? 'invoice-card--overdue' : ($invoice['is_due_soon'] ? 'invoice-card--duesoon' : '') }} {{ ($selected[$invoice['id']] ?? false) ? 'invoice-card--selected' : '' }}"
            wire:click="toggleSelect({{ $invoice['id'] }})"
        >
            @if ($invoice['is_overdue'])
            <div style="background:#d63939;color:white;font-size:0.7rem;font-weight:700;padding:3px 12px">🔴 TERLAMBAT {{ $invoice['days_overdue'] }} HARI</div>
            @elseif ($invoice['is_due_soon'])
            <div style="background:#f59f00;color:white;font-size:0.7rem;font-weight:700;padding:3px 12px">⏰ JATUH TEMPO {{ $invoice['days_to_due'] }} HARI LAGI</div>
            @endif
            <div class="invoice-card__body">
                <div class="invoice-check {{ ($selected[$invoice['id']] ?? false) ? 'invoice-check--checked' : '' }}">
                    @if ($selected[$invoice['id']] ?? false)
                    <span style="color:white;font-size:0.8rem;font-weight:700">✓</span>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div style="font-size:0.88rem;font-weight:600;color:#1a1a2e">{{ $invoice['invoice_number'] }}</div>
                    <div style="font-size:0.75rem;color:#9ca3af">
                        {{ \Carbon\Carbon::parse($invoice['invoice_date'])->format('d M Y') }}
                        · Tempo: {{ $invoice['credit_term_days_snapshot'] }}h
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:0.95rem;font-weight:700;color:#1a1a2e">Rp {{ number_format($invoice['remaining_amount'], 0, ',', '.') }}</div>
                    @if ($invoice['paid_amount'] > 0)
                    <div style="font-size:0.7rem;color:#9ca3af">Bayar: Rp {{ number_format($invoice['paid_amount'], 0, ',', '.') }}</div>
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
        @if ($selectedCount > 0)
        <div class="mb-3">
            <div class="falaya-card p-3">
                <div style="font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px">JUMLAH DITERIMA (alokasi FIFO otomatis)</div>
                @foreach ($invoices as $invoice)
                @if ($selected[$invoice['id']] ?? false)
                <div class="d-flex justify-content-between align-items-center py-1">
                    <div style="font-size:0.85rem;color:#374151">{{ $invoice['invoice_number'] }}</div>
                    <div style="position:relative;width:140px">
                        <span style="font-size:0.8rem;color:#9ca3af;position:absolute;left:0;top:6px">Rp</span>
                        <input
                            type="number"
                            wire:model.live="amounts.{{ $invoice['id'] }}"
                            style="width:100%;padding-left:24px;text-align:right;border:none;border-bottom:1px solid #d1d5db;font-weight:600;font-size:0.9rem"
                            min="0"
                            inputmode="numeric"
                        >
                    </div>
                </div>
                @endif
                @endforeach
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <div style="font-size:0.82rem;color:#616876">{{ $selectedCount }} invoice dipilih</div>
                    <div style="font-weight:700;color:#f59e0b">Rp {{ number_format($total, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Skip / Lewati section (collapsible, Alpine murni untuk toggle UI saja) ── --}}
        <div class="falaya-card mb-3" style="overflow:hidden">
            <div class="skip-toggle" @click="skipOpen = !skipOpen">
                <span class="skip-toggle-label">❌ Lewati Penagihan</span>
                <span style="font-size:0.85rem;color:#9ca3af;transition:transform 0.2s" :style="skipOpen ? 'transform:rotate(180deg)' : ''">▾</span>
            </div>
            <div x-show="skipOpen" x-collapse>
                <div class="skip-radio">
                    <input type="radio" id="reason1" name="skip_reason" value="no_money" wire:model="skipReason">
                    <label for="reason1">Tidak ada uang</label>
                </div>
                <div class="skip-radio">
                    <input type="radio" id="reason2" name="skip_reason" value="reschedule" wire:model="skipReason">
                    <label for="reason2">Minta reschedule</label>
                </div>
                <div class="skip-radio">
                    <input type="radio" id="reason3" name="skip_reason" value="already_transfer" wire:model="skipReason">
                    <label for="reason3">Sudah transfer sendiri</label>
                </div>
                <div class="skip-radio">
                    <input type="radio" id="reason4" name="skip_reason" value="other" wire:model="skipReason">
                    <label for="reason4">Lainnya</label>
                </div>
                @if ($skipReason === 'other')
                <div class="px-3 pb-3">
                    <textarea
                        wire:model="skipNote"
                        placeholder="Tulis catatan..."
                        class="form-control"
                        rows="2"
                        style="border-radius:8px;font-size:0.88rem"
                    ></textarea>
                </div>
                @endif
                <div class="px-3 pb-3">
                    <button
                        type="button"
                        wire:click="submitSkip"
                        wire:loading.attr="disabled"
                        class="w-100"
                        style="min-height:44px;border-radius:8px;border:1.5px solid #d63939;background:white;color:#d63939;font-weight:600;font-size:0.9rem{{ ! $skipReason ? ';opacity:0.5' : '' }}"
                        {{ ! $skipReason ? 'disabled' : '' }}
                    >
                        Lewati Penagihan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Sticky Bottom Bar ────────────────────────────────────── --}}
    <div class="sticky-bottom-bar">
        @if ($selectedCount > 0)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div style="font-size:0.82rem;color:#616876">Akan dibayar</div>
            <div style="font-size:1.1rem;font-weight:800;color:#f59e0b">Rp {{ number_format($total, 0, ',', '.') }}</div>
        </div>
        @endif
        <button
            wire:click="submitPayment"
            wire:loading.attr="disabled"
            class="btn-submit"
            {{ $selectedCount === 0 || $total <= 0 ? 'disabled' : '' }}
        >
            @if ($selectedCount === 0)
            Pilih invoice untuk melanjutkan
            @else
            💰 Simpan Pembayaran
            @endif
        </button>
    </div>
    <div style="height:20px"></div>
</div>
