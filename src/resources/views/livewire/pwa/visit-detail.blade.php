<div
    x-data="{
        status: '{{ $visitPlan->status }}',
        gpsState: 'idle',
        gpsError: null,
        gpsInfo: null,
        checkinLoading: false,
        checkoutLoading: false,
        csrfToken: '{{ csrf_token() }}',

        async doCheckin() {
            this.checkinLoading = true;
            this.gpsState = 'loading';
            this.gpsError = null;

            let gpsData = { unavailable: false };

            try {
                const pos = await new Promise((resolve) => {
                    const timer = setTimeout(() => resolve(null), 12000);
                    navigator.geolocation.getCurrentPosition(
                        p => { clearTimeout(timer); resolve(p); },
                        () => { clearTimeout(timer); resolve(null); },
                        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
                    );
                });

                if (pos === null) {
                    gpsData = { unavailable: true };
                    this.gpsState = 'unavailable';
                } else {
                    gpsData = {
                        unavailable: false,
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy: pos.coords.accuracy,
                    };
                    this.gpsState = 'ok';
                    this.gpsInfo = 'Akurasi: ' + Math.round(pos.coords.accuracy) + 'm';
                }
            } catch (e) {
                gpsData = { unavailable: true };
                this.gpsState = 'unavailable';
            }

            try {
                const res = await fetch('/pwa/api/visits/checkin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        visit_plan_id: {{ $visitPlan->id }},
                        idempotency_key: crypto.randomUUID(),
                        gps_unavailable: gpsData.unavailable,
                        latitude: gpsData.latitude ?? null,
                        longitude: gpsData.longitude ?? null,
                        accuracy: gpsData.accuracy ?? null,
                    }),
                });

                const data = await res.json();

                if (res.ok) {
                    this.status = 'IN_PROGRESS';
                    this.gpsError = null;
                } else {
                    this.gpsError = data.message ?? 'Check-in gagal.';
                    this.gpsState = 'error';
                }
            } catch (e) {
                this.gpsError = 'Koneksi bermasalah. Coba lagi.';
                this.gpsState = 'error';
            }

            this.checkinLoading = false;
        },

        async doCheckout() {
            this.checkoutLoading = true;
            this.gpsError = null;

            let lat = null, lng = null;
            try {
                const pos = await new Promise((resolve) => {
                    const timer = setTimeout(() => resolve(null), 8000);
                    navigator.geolocation.getCurrentPosition(
                        p => { clearTimeout(timer); resolve(p); },
                        () => { clearTimeout(timer); resolve(null); },
                        { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
                    );
                });
                if (pos) { lat = pos.coords.latitude; lng = pos.coords.longitude; }
            } catch (e) {}

            try {
                const res = await fetch('/pwa/api/visits/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        visit_plan_id: {{ $visitPlan->id }},
                        latitude: lat,
                        longitude: lng,
                    }),
                });

                const data = await res.json();

                if (res.ok) {
                    this.status = data.visit_status ?? 'COMPLETED';
                } else {
                    this.gpsError = data.message ?? 'Check-out gagal.';
                }
            } catch (e) {
                this.gpsError = 'Koneksi bermasalah. Coba lagi.';
            }

            this.checkoutLoading = false;
        },
    }"
>
    <div class="pwa-header">
        <a href="/pwa/visits" class="text-white text-decoration-none opacity-75">← Kembali</a>
        <h5 class="mt-1">{{ $visitPlan->customer->customer_name }}</h5>
        <small>{{ $visitPlan->customer->address }}</small>
    </div>

    <div class="px-3">

        <div class="mb-3">
            <span :class="{
                'badge fs-6 badge-status-planned': status === 'PLANNED',
                'badge fs-6 badge-status-in_progress': status === 'IN_PROGRESS',
                'badge fs-6 badge-status-completed': status === 'COMPLETED',
                'badge fs-6 badge-status-no_order': status === 'NO_ORDER',
                'badge fs-6 badge-status-outlet_closed': status === 'OUTLET_CLOSED',
            }" x-text="status"></span>
        </div>

        <div class="falaya-card">
            <div class="card-body">
                <div class="falaya-card__title mb-2">📍 Info Outlet</div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="falaya-card__subtitle">Tipe</span>
                    <strong>{{ $visitPlan->customer->customer_type }}</strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="falaya-card__subtitle">Radius toleransi</span>
                    <strong>{{ $visitPlan->customer->radius_tolerance_meter ?? 100 }}m</strong>
                </div>
                @if($visitPlan->customer->credit_limit)
                <div class="d-flex justify-content-between py-1">
                    <span class="falaya-card__subtitle">Limit kredit</span>
                    <strong>Rp {{ number_format($visitPlan->customer->credit_limit, 0, ',', '.') }}</strong>
                </div>
                @endif
            </div>
        </div>

        <div x-show="gpsError" x-cloak class="falaya-card falaya-card--danger">
            <div class="card-body">
                <div class="falaya-card__title">❌ <span x-text="gpsError"></span></div>
                <button class="btn btn-outline-danger btn-sm mt-2 w-100" @click="gpsError = null; doCheckin()">Coba Lagi</button>
            </div>
        </div>

        <div x-show="gpsState === 'unavailable' && !gpsError" x-cloak class="falaya-card falaya-card--warning">
            <div class="card-body">
                <div class="falaya-card__subtitle">⚠️ Lokasi tidak dapat dideteksi. Check-in tetap diizinkan tanpa lokasi.</div>
            </div>
        </div>

        <div x-show="gpsState === 'ok'" x-cloak class="falaya-card falaya-card--success">
            <div class="card-body">
                <div class="falaya-card__subtitle">✅ Lokasi terverifikasi — <span x-text="gpsInfo"></span></div>
            </div>
        </div>

        <div x-show="status === 'PLANNED'" class="d-grid mt-2">
            <button class="btn btn-primary" :disabled="checkinLoading" @click="doCheckin()">
                <span x-show="gpsState === 'loading'">📡 Mengambil lokasi GPS...</span>
                <span x-show="gpsState !== 'loading'" x-text="checkinLoading ? 'Memproses...' : '📍 Check-in Sekarang'"></span>
            </button>
        </div>

        <div x-show="status === 'IN_PROGRESS'" class="d-grid mt-2">
            <button class="btn btn-success" :disabled="checkoutLoading" @click="doCheckout()">
                <span x-text="checkoutLoading ? 'Memproses...' : '✅ Check-out'"></span>
            </button>
        </div>

        <div x-show="status === 'IN_PROGRESS'" class="d-grid mt-2">
            <a href="/pwa/visits/{{ $visitPlan->id }}/order" class="btn btn-primary btn-lg">🛒 Buat Pesanan</a>
        </div>

        <div x-show="status === 'IN_PROGRESS'" class="d-grid mt-2">
            <a href="/pwa/visits/{{ $visitPlan->id }}/collection" class="btn btn-outline-success btn-lg">💰 Catat Pembayaran</a>
        </div>

        <div x-show="['COMPLETED','NO_ORDER','OUTLET_CLOSED'].includes(status)" x-cloak class="falaya-card falaya-card--success mt-2">
            <div class="card-body text-center">
                <div class="falaya-card__title">✅ Kunjungan selesai</div>
                <a href="/pwa/visits" class="btn btn-outline-success btn-sm mt-2">← Kembali ke Daftar</a>
            </div>
        </div>

    </div>
</div>
