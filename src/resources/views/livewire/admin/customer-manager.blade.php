<div>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Manajemen Customer</h2>
                </div>
                <div class="col-auto ms-auto">
                    <button class="btn btn-primary" wire:click="openCreate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                        Tambah Customer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible mb-3">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($showForm)
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ $isEdit ? 'Edit Customer' : 'Tambah Customer Baru' }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label required">Kode Customer</label>
                                <input type="text" class="form-control @error('customer_code') is-invalid @enderror"
                                    wire:model="customer_code" placeholder="CST-001">
                                @error('customer_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label required">Nama Outlet</label>
                                <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                    wire:model="customer_name" placeholder="Toko Sumber Rejeki">
                                @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Area</label>
                                <select class="form-select @error('area_id') is-invalid @enderror" wire:model="area_id">
                                    <option value="">-- Pilih Area --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->area_name }}</option>
                                    @endforeach
                                </select>
                                @error('area_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label required">Alamat</label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                    wire:model="address" rows="2" placeholder="Jl. Merdeka No. 12"></textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">Tipe</label>
                                <select class="form-select" wire:model.live="customer_type">
                                    <option value="CASH">CASH</option>
                                    <option value="CREDIT">CREDIT</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="0.0000001" id="map-lat"
                                    class="form-control @error('latitude') is-invalid @enderror"
                                    wire:model="latitude" placeholder="-6.2000000">
                                @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="0.0000001" id="map-lng"
                                    class="form-control @error('longitude') is-invalid @enderror"
                                    wire:model="longitude" placeholder="106.8000000">
                                @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Radius Toleransi (meter)</label>
                                <input type="number" id="map-radius"
                                    class="form-control @error('radius_tolerance_meter') is-invalid @enderror"
                                    wire:model="radius_tolerance_meter" placeholder="100">
                                @error('radius_tolerance_meter') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">
                                    Pin Lokasi Outlet
                                    <span class="text-muted small ms-1">— klik peta untuk pindah pin, atau gunakan tombol di bawah</span>
                                </label>
                                <div class="mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="useMyLocation()">
                                        📍 Gunakan Lokasi Saya
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="resetMap()">
                                        🔄 Reset Peta
                                    </button>
                                </div>
                                <div id="outlet-map" style="height: 300px; border-radius: 8px; border: 1px solid #dee2e6; z-index:0;"></div>
                            </div>

                            @if($customer_type === 'CREDIT')
                                <div class="col-md-4">
                                    <label class="form-label required">Limit Kredit</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('credit_limit') is-invalid @enderror"
                                            wire:model="credit_limit" min="0" step="10000">
                                        @error('credit_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label required">Tempo (hari)</label>
                                    <select class="form-select @error('credit_term_days') is-invalid @enderror"
                                        wire:model="credit_term_days">
                                        <option value="">-- Pilih --</option>
                                        <option value="7">7 hari</option>
                                        <option value="14">14 hari</option>
                                        <option value="30">30 hari</option>
                                    </select>
                                    @error('credit_term_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Nama Pemilik</label>
                                    <input type="text" class="form-control" wire:model="owner_name" placeholder="Pak Budi">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">No. HP Pemilik</label>
                                    <input type="text" class="form-control" wire:model="owner_phone" placeholder="08123456789">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Customer' }}
                        </button>
                        <button class="btn btn-secondary" wire:click="cancelForm">Batal</button>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header gap-2 flex-wrap">
                    <div class="input-group" style="max-width:300px">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
                        </span>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                            placeholder="Cari customer...">
                    </div>
                    <select class="form-select" style="max-width:150px" wire:model.live="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="ACTIVE">Aktif</option>
                        <option value="PENDING_APPROVAL">Pending</option>
                        <option value="INACTIVE">Nonaktif</option>
                        <option value="REJECTED">Ditolak</option>
                    </select>
                    <select class="form-select" style="max-width:130px" wire:model.live="filterType">
                        <option value="">Semua Tipe</option>
                        <option value="CASH">Cash</option>
                        <option value="CREDIT">Credit</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Outlet</th>
                                <th>Area</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr class="{{ $customer->trashed() ? 'opacity-50' : '' }}">
                                    <td><code>{{ $customer->customer_code }}</code></td>
                                    <td>
                                        {{ $customer->customer_name }}
                                        @if($customer->credit_limit)
                                            <small class="text-muted d-block">Limit: Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $customer->area->area_name ?? '-' }}</td>
                                    <td><span class="badge {{ $customer->customer_type === 'CREDIT' ? 'bg-blue' : 'bg-green' }} text-white">{{ $customer->customer_type }}</span></td>
                                    <td>
                                        @php
                                            $statusClass = match($customer->status) {
                                                'ACTIVE' => 'bg-success',
                                                'PENDING_APPROVAL' => 'bg-warning',
                                                'REJECTED' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $customer->status }}</span>
                                    </td>
                                    <td>
                                        @if(!$customer->trashed())
                                            <button class="btn btn-sm btn-outline-primary"
                                                wire:click="openEdit({{ $customer->id }})">Edit</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Belum ada customer.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($customers->hasPages())
                    <div class="card-footer">{{ $customers->links() }}</div>
                @endif
            </div>

        </div>
    </div>
    @if($showForm)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let outletMap = null;
        let outletMarker = null;
        let outletCircle = null;

        function initMap() {
            if (outletMap) {
                outletMap.remove();
                outletMap = null;
                outletMarker = null;
                outletCircle = null;
            }

            const latVal = parseFloat(document.getElementById("map-lat")?.value);
            const lngVal = parseFloat(document.getElementById("map-lng")?.value);
            const hasCoord = !isNaN(latVal) && !isNaN(lngVal);

            const center = hasCoord ? [latVal, lngVal] : [-6.2088, 106.8456];
            const zoom = hasCoord ? 17 : 12;

            outletMap = L.map("outlet-map").setView(center, zoom);

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "© OpenStreetMap contributors",
                maxZoom: 19,
            }).addTo(outletMap);

            if (hasCoord) {
                placeMarker(latVal, lngVal);
            }

            outletMap.on("click", function (e) {
                placeMarker(e.latlng.lat, e.latlng.lng);
                updateInputs(e.latlng.lat, e.latlng.lng);
            });
        }

        function placeMarker(lat, lng) {
            if (outletMarker) {
                outletMap.removeLayer(outletMarker);
            }
            if (outletCircle) {
                outletMap.removeLayer(outletCircle);
            }

            outletMarker = L.marker([lat, lng], { draggable: true }).addTo(outletMap);
            outletMarker.bindPopup("📍 Lokasi Outlet").openPopup();

            outletMarker.on("dragend", function (e) {
                const pos = e.target.getLatLng();
                updateInputs(pos.lat, pos.lng);
                drawRadius(pos.lat, pos.lng);
            });

            drawRadius(lat, lng);
        }

        function drawRadius(lat, lng) {
            if (outletCircle) {
                outletMap.removeLayer(outletCircle);
            }
            const radiusInput = document.getElementById("map-radius");
            const radius = parseInt(radiusInput?.value) || 100;
            outletCircle = L.circle([lat, lng], {
                radius: radius,
                color: "#206bc4",
                fillColor: "#206bc4",
                fillOpacity: 0.1,
                weight: 2,
            }).addTo(outletMap);
        }

        function updateInputs(lat, lng) {
            const latInput = document.getElementById("map-lat");
            const lngInput = document.getElementById("map-lng");
            if (latInput) {
                latInput.value = lat.toFixed(7);
                latInput.dispatchEvent(new Event("input"));
            }
            if (lngInput) {
                lngInput.value = lng.toFixed(7);
                lngInput.dispatchEvent(new Event("input"));
            }
        }

        function useMyLocation() {
            if (!navigator.geolocation) {
                alert("Browser tidak mendukung geolocation.");
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    updateInputs(lat, lng);
                    if (outletMap) {
                        outletMap.setView([lat, lng], 17);
                        placeMarker(lat, lng);
                    }
                },
                function () {
                    alert("Gagal mendapatkan lokasi. Pastikan izin lokasi diberikan.");
                }
            );
        }

        function resetMap() {
            if (outletMarker) outletMap.removeLayer(outletMarker);
            if (outletCircle) outletMap.removeLayer(outletCircle);
            outletMarker = null;
            outletCircle = null;
            updateInputs("", "");
            outletMap.setView([-6.2088, 106.8456], 12);
        }

        // Init peta saat form muncul, dan saat Livewire update
        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(initMap, 100);
        });

        document.addEventListener("livewire:navigated", function () {
            setTimeout(initMap, 100);
        });

        Livewire.on("formOpened", function () {
            setTimeout(initMap, 150);
        });

        // Redraw radius saat input radius berubah
        document.addEventListener("input", function (e) {
            if (e.target.id === "map-radius" && outletMarker) {
                const pos = outletMarker.getLatLng();
                drawRadius(pos.lat, pos.lng);
            }
        });
    </script>
    @endif
</div>