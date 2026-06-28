<div>
    {{-- ============================================================
         PWA Visit Detail — Falaya SFA
         Livewire + Alpine.js for GPS check-in flow
         ============================================================ --}}

    <style>
        .falaya-card { border-radius: 12px; border: 1px solid #e6e7e9; margin-bottom: 12px; background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .falaya-card--danger  { border-color: #d63939; background: #fff5f5; }
        .falaya-card--success { border-color: #2fb344; background: #f4fdf5; }
        .falaya-card--warning { border-color: #f59f00; background: #fffbf0; }
        .falaya-card--info    { border-color: #206bc4; background: #f0f6ff; }
        .falaya-card__title   { font-weight: 600; font-size: 1rem; color: #1a1a2e; }
        .falaya-card__subtitle { font-size: 0.875rem; color: #616876; }
        .action-btn-primary { width: 100%; min-height: 52px; border-radius: 10px; border: none; background: #206bc4; color: white; font-weight: 600; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .action-btn-primary:active { background: #1a5aa8; }
        .action-btn-outline { width: 100%; min-height: 52px; border-radius: 10px; border: 2px solid #206bc4; background: white; color: #206bc4; font-weight: 600; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .action-btn-outline:active { background: #f0f6ff; }
        .action-btn-danger { width: 100%; min-height: 52px; border-radius: 10px; border: none; background: #d63939; color: white; font-weight: 600; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .gps-loading { display: flex; flex-direction: column; align-items: center; padding: 24px; gap: 12px; }
        .spinner-ring { width: 40px; height: 40px; border: 3px solid #e6e7e9; border-top-color: #206bc4; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .invoice-mini { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .invoice-mini:last-child { border-bottom: none; }
        .credit-bar { height: 8px; border-radius: 4px; background: #e6e7e9; overflow: hidden; margin-top: 6px; }
        .credit-bar__fill { height: 100%; border-radius: 4px; background: #206bc4; transition: width 0.3s; }
        .small-link { font-size: 0.82rem; color: #616876; text-decoration: none; text-align: center; display: block; padding: 6px; }
        .small-link:active { color: #374151; }
        .back-btn { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #616876; text-decoration: none; padding: 12px 16px 4px; }
    </style>

    {{-- ── Back nav ─────────────────────────────────────────────────── --}}
    <a href="{{ route('pwa.pages.visits') }}" class="back-btn">
        ‹ Daftar Kunjungan
    </a>

    {{-- ── Outlet Header Card ──────────────────────────────────────── --}}
    <div class="px-3 pt-1">
        <div class="falaya-card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="falaya-card__title" style="font-size:1.05rem">{{ $visit->customer->customer_name }}</div>
                <span style="font-size:0.72rem;font-weight:600;padding:3px 9px;border-radius:20px;background:{{ $visit->customer->customer_type === 'CREDIT' ? '#dbeafe' : '#dcfce7' }};color:{{ $visit->customer->customer_type === 'CREDIT' ? '#1d4ed8' : '#15803d' }}">
                    {{ $visit->customer->customer_type === 'CREDIT' ? '💳 KREDIT' : '💵 CASH' }}
                </span>
            </div>
            <div class="falaya-card__subtitle">{{ $visit->customer->address }}</div>
            <div style="font-size:0.78rem;color:#9ca3af;margin-top:4px">📍 {{ $visit->customer->area->area_name }}</div>
        </div>
    </div>

    {{-- ── Credit Info (only for CREDIT customers) ─────────────────── --}}
    @if ($visit->customer->customer_type === 'CREDIT' && $visit->customer->credit_limit)
    @php
        $used = $customerOutstanding ?? 0;
        $limit = $visit->customer->credit_limit;
        $remaining = max(0, $limit - $used);
        $usedPct = $limit > 0 ? min(100, round($used / $limit * 100)) : 0;
        $barColor = $usedPct >= 90 ? '#d63939' : ($usedPct >= 70 ? '#f59f00' : '#206bc4');
    @endphp
    <div class="px-3">
        <div class="falaya-card {{ $usedPct >= 90 ? 'falaya-card--danger' : 'falaya-card--info' }} p-3 mb-3">
            <div style="font-size:0.75rem;font-weight:600;color:#6b7280;margin-bottom:6px">💳 LIMIT KREDIT</div>
            <div class="d-flex justify-content-between align-items-baseline mb-1">
                <div>
                    <span style="font-size:0.78rem;color:#6b7280">Terpakai</span>
                    <span style="font-size:0.95rem;font-weight:700;color:#1a1a2e;margin-left:4px">Rp {{ number_format($used, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span style="font-size:0.78rem;color:#6b7280">Sisa</span>
                    <span style="font-size:0.95rem;font-weight:700;color:{{ $remaining > 0 ? '#15803d' : '#d63939' }};margin-left:4px">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="credit-bar">
                <div class="credit-bar__fill" style="width:{{ $usedPct }}%;background:{{ $barColor }}"></div>
            </div>
            <div style="font-size:0.72rem;color:#9ca3af;margin-top:4px">Dari limit Rp {{ number_format($limit, 0, ',', '.') }}</div>
        </div>
    </div>
    @endif

    {{-- ── Main State Machine — Alpine.js ──────────────────────────── --}}
    @php
        $isCheckedIn  = in_array($visit->status, ['IN_PROGRESS', 'COMPLETED', 'NO_ORDER', 'OUTLET_CLOSED']);
        $isDone       = in_array($visit->status, ['COMPLETED', 'NO_ORDER', 'OUTLET_CLOSED', 'SKIPPED']);
    @endphp

    <div
        class="px-3"
        x-data="{
            gpsState: '{{ $isCheckedIn ? 'done' : 'idle' }}',
            {{-- idle | loading | success | outside_radius | unavailable | done --}}
            gpsInfo: null,
            photoTaken: false,
            showPhotoCapture: false,

            async startCheckin() {
                this.gpsState = 'loading';
                const timeout = new Promise(resolve => setTimeout(() => resolve({ unavailable: true }), 12000));
                const gpsPromise = new Promise(resolve => {
                    navigator.geolocation.getCurrentPosition(
                        pos => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy }),
                        ()  => resolve({ unavailable: true }),
                        { enableHighAccuracy: true, timeout: 11000 }
                    );
                });
                const result = await Promise.race([gpsPromise, timeout]);

                if (result.unavailable) {
                    this.gpsState = 'unavailable';
                    this.gpsInfo  = null;
                    return;
                }

                this.gpsInfo = result;
                const dist = this.haversine(result.lat, result.lng, {{ $visit->customer->latitude ?? 0 }}, {{ $visit->customer->longitude ?? 0 }});
                const radius = {{ $visit->customer->radius_tolerance_meter ?? $defaultRadius ?? 100 }};

                if (dist > radius) {
                    this.gpsState = 'outside_radius';
                    this.gpsInfo  = { ...result, distance: Math.round(dist), radius };
                } else {
                    this.gpsState = 'success';
                    this.gpsInfo  = { ...result, distance: Math.round(dist) };
                    this.showPhotoCapture = true;
                }
            },

            haversine(lat1, lon1, lat2, lon2) {
                const R = 6371000;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLon/2)**2;
                return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            },

            async confirmCheckin() {
                const data = this.gpsInfo ?? { unavailable: true };
                {{-- delegate to Livewire, tunggu response server sebelum
                     update UI state -- tanpa await, gpsState bisa nyangkut
                     di state lama walau server sudah sukses ubah status --}}
                await @this.call('checkin', data);
                this.gpsState = 'done';
            }
        }"
    >

        {{-- ── STATE: idle (not yet checked in) ──────────────────── --}}
        <template x-if="gpsState === 'idle'">
            <div>
                <button @click="startCheckin()" class="action-btn-primary mb-3">
                    📍 Check-in Sekarang
                </button>
            </div>
        </template>

        {{-- ── STATE: loading GPS ───────────────────────────────── --}}
        <template x-if="gpsState === 'loading'">
            <div class="falaya-card falaya-card--info p-3 mb-3">
                <div class="gps-loading">
                    <div class="spinner-ring"></div>
                    <div style="font-weight:600;color:#206bc4">📡 Mengambil GPS...</div>
                    <div style="font-size:0.82rem;color:#3b5ea6">Tunggu sebentar</div>
                </div>
            </div>
        </template>

        {{-- ── STATE: GPS success — photo step ─────────────────── --}}
        <template x-if="gpsState === 'success'">
            <div>
                <div class="falaya-card falaya-card--success p-3 mb-2">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span style="font-size:1.2rem">✅</span>
                        <div class="falaya-card__title" style="color:#15803d">Lokasi Terverifikasi</div>
                    </div>
                    <div style="font-size:0.85rem;color:#166534" x-text="'Jarak ' + gpsInfo.distance + 'm dari outlet'"></div>
                    <div style="font-size:0.78rem;color:#4ade80;margin-top:2px" x-show="gpsInfo.acc <= 20">Akurasi GPS sangat baik</div>
                </div>

                {{-- Photo capture --}}
                <div class="falaya-card p-3 mb-3" x-show="!photoTaken">
                    <div style="font-size:0.85rem;color:#616876;margin-bottom:10px">📷 <strong>Ambil foto outlet</strong> sebagai bukti kunjungan</div>
                    <label style="display:block;cursor:pointer">
                        <input type="file" accept="image/*" capture="environment" class="d-none"
                            wire:model="visitPhoto"
                            @change="photoTaken = true">
                        <div style="border:2px dashed #206bc4;border-radius:10px;padding:24px;text-align:center;color:#206bc4">
                            <div style="font-size:2rem;margin-bottom:4px">📷</div>
                            <div style="font-weight:600;font-size:0.9rem">Buka Kamera</div>
                            <div style="font-size:0.75rem;color:#9ca3af;margin-top:2px">Foto tampak depan outlet</div>
                        </div>
                    </label>
                </div>

                <div class="falaya-card falaya-card--success p-3 mb-3" x-show="photoTaken">
                    <div class="d-flex align-items-center gap-2">
                        <span>📸</span>
                        <div>
                            <div style="font-weight:600;color:#15803d;font-size:0.9rem">Foto berhasil diambil</div>
                            <div style="font-size:0.78rem;color:#166534">Siap untuk check-in</div>
                        </div>
                    </div>
                </div>

                <button
                    class="action-btn-primary mb-2"
                    @click="confirmCheckin()"
                    :disabled="!photoTaken"
                    :style="!photoTaken ? 'opacity:0.5' : ''"
                >
                    ✅ Konfirmasi Check-in
                </button>
                <div style="font-size:0.78rem;color:#9ca3af;text-align:center;margin-bottom:12px" x-show="!photoTaken">
                    Ambil foto terlebih dahulu
                </div>
            </div>
        </template>

        {{-- ── STATE: outside radius — BLOCKED ─────────────────── --}}
        <template x-if="gpsState === 'outside_radius'">
            <div>
                <div class="falaya-card falaya-card--danger p-3 mb-3">
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <span style="font-size:1.3rem">❌</span>
                        <div>
                            <div class="falaya-card__title" style="color:#991b1b">Tidak Bisa Check-in</div>
                            <div style="font-size:0.85rem;color:#7f1d1d;margin-top:4px" x-text="'Jarak Anda ' + gpsInfo.distance + 'm dari outlet, melebihi radius ' + gpsInfo.radius + 'm yang diizinkan.'"></div>
                        </div>
                    </div>
                    <div style="font-size:0.82rem;color:#9b1c1c">Pastikan Anda berada di lokasi outlet, lalu coba lagi.</div>
                </div>
                <button @click="gpsState = 'idle'" class="action-btn-primary mb-3" style="background:#d63939">
                    🔄 Coba Lagi
                </button>
            </div>
        </template>

        {{-- ── STATE: GPS unavailable — allowed with flag ──────── --}}
        <template x-if="gpsState === 'unavailable'">
            <div>
                <div class="falaya-card falaya-card--warning p-3 mb-3">
                    <div class="d-flex align-items-start gap-2">
                        <span style="font-size:1.2rem">⚠️</span>
                        <div>
                            <div class="falaya-card__title" style="color:#92400e">Lokasi Tidak Terdeteksi</div>
                            <div style="font-size:0.85rem;color:#78350f;margin-top:4px">Anda tetap bisa check-in, namun lokasi tidak akan tercatat untuk kunjungan ini.</div>
                        </div>
                    </div>
                </div>
                <button @click="confirmCheckin()" class="action-btn-primary mb-2" style="background:#f59f00">
                    ⚠️ Tetap Check-in Tanpa GPS
                </button>
                <button @click="gpsState = 'idle'" class="action-btn-outline mb-3">
                    🔄 Coba Lagi
                </button>
            </div>
        </template>

        {{-- ── STATE: done / checked in ──────────────────────────── --}}
        @if ($isCheckedIn && !$isDone)
        <div>
            {{-- Outstanding warning --}}
            @if ($outstandingTotal > 0)
            <div class="falaya-card falaya-card--warning p-3 mb-3">
                <div style="font-size:0.75rem;font-weight:600;color:#92400e;margin-bottom:8px">⚠️ TAGIHAN OUTSTANDING</div>
                <div style="font-size:1.25rem;font-weight:700;color:#92400e;margin-bottom:8px">Rp {{ number_format($outstandingTotal, 0, ',', '.') }}</div>
                @foreach ($outstandingInvoices->take(2) as $inv)
                <div class="invoice-mini">
                    <div>
                        <div style="font-size:0.82rem;font-weight:600;color:#374151">{{ $inv['invoice_number'] }}</div>
                        <div style="font-size:0.75rem;color:#6b7280">Rp {{ number_format($inv['remaining_amount'], 0, ',', '.') }}</div>
                    </div>
                    @if ($inv['is_overdue'])
                    <span style="font-size:0.7rem;padding:2px 7px;border-radius:20px;background:#fee2e2;color:#991b1b;font-weight:600">🔴 Terlambat {{ $inv['days_overdue'] }}h</span>
                    @elseif ($inv['due_soon'])
                    <span style="font-size:0.7rem;padding:2px 7px;border-radius:20px;background:#fef9c3;color:#854d0e;font-weight:600">⏰ {{ $inv['days_to_due'] }}h lagi</span>
                    @endif
                </div>
                @endforeach

                <a href="{{ route('pwa.pages.visits.collection', $visit->id) }}"
                   class="d-block w-100 mt-3 py-2 text-center fw-600"
                   style="background:#f59f00;color:white;border-radius:8px;text-decoration:none;font-weight:600;min-height:44px;line-height:2.5">
                    💰 Tagih Sekarang
                </a>
            </div>
            @endif

            {{-- Primary actions --}}
            <a href="{{ route('pwa.pages.visits.order', $visit->id) }}" class="action-btn-primary mb-2 d-flex" style="text-decoration:none">
                🛒 Buat Pesanan
            </a>
            <a href="{{ route('pwa.pages.visits.collection', $visit->id) }}" class="action-btn-outline mb-3 d-flex" style="text-decoration:none">
                💰 Catat Pembayaran
            </a>

            {{-- Secondary actions --}}
            <div class="d-flex justify-content-around">
                <button wire:click="markOutletClosed" class="small-link">🔒 Outlet Tutup</button>
                <span style="color:#e6e7e9">|</span>
                <button wire:click="markNoOrder" class="small-link">👋 Tanpa Order</button>
                <span style="color:#e6e7e9">|</span>
                <button wire:click="checkout" class="small-link" style="color:#206bc4">✅ Check-out</button>
            </div>
        </div>
        @endif

        {{-- ── Done state ──────────────────────────────────────────── --}}
        @if ($isDone)
        <div class="falaya-card falaya-card--success p-3 mb-3">
            <div class="text-center">
                <div style="font-size:2rem;margin-bottom:6px">✅</div>
                <div style="font-weight:600;color:#15803d">Kunjungan Selesai</div>
                @switch($visit->status)
                    @case('COMPLETED')   <div style="font-size:0.85rem;color:#166534">Ada order yang dibuat</div> @break
                    @case('NO_ORDER')    <div style="font-size:0.85rem;color:#166534">Selesai tanpa order</div> @break
                    @case('OUTLET_CLOSED') <div style="font-size:0.85rem;color:#166534">Outlet tutup</div> @break
                @endswitch
            </div>
        </div>
        <a href="{{ route('pwa.pages.visits') }}" class="action-btn-outline d-flex" style="text-decoration:none">
            ‹ Kembali ke Daftar
        </a>
        @endif

    </div>

    <div style="height:80px"></div>
</div>
