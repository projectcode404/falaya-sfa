<div>
    {{-- Header --}}
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Manajemen Customer</h2>
            <p class="text-xs text-slate-400">Kelola data outlet dan customer</p>
        </div>
        <button wire:click="openCreate"
                class="flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-slate-800">
            <x-heroicon-m-plus class="h-4 w-4"/>
            Tambah Customer
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <x-heroicon-o-check-circle class="h-4 w-4 flex-shrink-0 text-emerald-500"/>
        {{ session('success') }}
    </div>
    @endif

    {{-- Form --}}
    @if($showForm)
    <div class="mb-5 rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-sm font-semibold text-slate-900">{{ $isEdit ? 'Edit Customer' : 'Tambah Customer Baru' }}</h3>
        </div>
        <div class="grid grid-cols-1 gap-4 px-5 py-4 md:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kode Customer <span class="text-red-500">*</span></label>
                <input type="text" wire:model="customer_code" placeholder="CST-001"
                       {{ !$isEdit ? 'readonly' : '' }}
                       class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                              {{ $errors->has('customer_code') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}
                              {{ !$isEdit ? 'bg-slate-50 text-slate-400' : '' }}">
                @if(!$isEdit)<p class="mt-1 text-xs text-slate-400">Kode otomatis, bisa diubah.</p>@endif
                @error('customer_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama Outlet <span class="text-red-500">*</span></label>
                <input type="text" wire:model="customer_name" placeholder="Toko Sumber Rejeki"
                       class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                              {{ $errors->has('customer_name') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                @error('customer_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Area <span class="text-red-500">*</span></label>
                <select wire:model="area_id"
                        class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                               {{ $errors->has('area_id') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                    <option value="">-- Pilih Area --</option>
                    @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->area_name }}</option>
                    @endforeach
                </select>
                @error('area_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-4">
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Alamat <span class="text-red-500">*</span></label>
                <textarea wire:model="address" rows="2" placeholder="Jl. Merdeka No. 12"
                          class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                                 {{ $errors->has('address') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}"></textarea>
                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Tipe <span class="text-red-500">*</span></label>
                <select wire:model="customer_type"
                        x-on:change="$wire.set('customer_type', $event.target.value); setTimeout(() => window.dispatchEvent(new CustomEvent('formOpened')), 50);"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    <option value="CASH">CASH</option>
                    <option value="CREDIT">CREDIT</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Latitude</label>
                <input type="number" step="0.0000001" id="map-lat" wire:model="latitude" placeholder="-6.2000000"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                @error('latitude') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Longitude</label>
                <input type="number" step="0.0000001" id="map-lng" wire:model="longitude" placeholder="106.8000000"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                @error('longitude') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Radius Toleransi (meter)</label>
                <input type="number" id="map-radius" wire:model="radius_tolerance_meter" placeholder="100"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
            </div>

            {{-- Peta --}}
            <div class="md:col-span-4">
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">
                    Pin Lokasi Outlet
                    <span class="font-normal text-slate-400">— klik peta untuk pindah pin</span>
                </label>
                <div class="mb-2 flex gap-2">
                    <button type="button" onclick="useMyLocation()"
                            class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                        <x-heroicon-o-map-pin class="h-3.5 w-3.5"/>
                        Gunakan Lokasi Saya
                    </button>
                    <button type="button" onclick="resetMap()"
                            class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                        <x-heroicon-o-arrow-path class="h-3.5 w-3.5"/>
                        Reset Peta
                    </button>
                </div>
                <div id="outlet-map" style="height: 300px; border-radius: 12px; border: 1px solid #e2e8f0; z-index:0;"></div>
            </div>

            {{-- Credit fields --}}
            <template x-if="$wire.customer_type === 'CREDIT'">
                <div class="md:col-span-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Limit Kredit <span class="text-red-500">*</span></label>
                        <div class="flex items-center overflow-hidden rounded-lg border {{ $errors->has('credit_limit') ? 'border-red-300' : 'border-slate-200' }}">
                            <span class="border-r border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold text-slate-500">Rp</span>
                            <input type="number" wire:model="credit_limit" min="0" step="10000"
                                   class="flex-1 px-3 py-2.5 text-sm focus:outline-none">
                        </div>
                        @error('credit_limit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Tempo <span class="text-red-500">*</span></label>
                        <select wire:model="credit_term_days"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                            <option value="">-- Pilih --</option>
                            <option value="7">7 hari</option>
                            <option value="14">14 hari</option>
                            <option value="30">30 hari</option>
                        </select>
                        @error('credit_term_days') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama Pemilik</label>
                        <input type="text" wire:model="owner_name" placeholder="Pak Budi"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">No. HP Pemilik</label>
                        <input type="text" wire:model="owner_phone" placeholder="08123456789"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    </div>
                </div>
            </template>
        </div>
        <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
            <button wire:click="save" wire:loading.attr="disabled"
                    class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-slate-800 disabled:opacity-50">
                <span wire:loading wire:target="save">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                </span>
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Customer' }}
            </button>
            <button wire:click="cancelForm"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                Batal
            </button>
        </div>
    </div>
    @endif

    {{-- Filters + Table --}}
    <div class="rounded-xl border border-slate-200 bg-white">
        <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-5 py-3">
            <div class="relative flex-1 min-w-48">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"/>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari customer..."
                       class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
            </div>
            <select wire:model.live="filterStatus"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                <option value="">Semua Status</option>
                <option value="ACTIVE">Aktif</option>
                <option value="PENDING_APPROVAL">Pending</option>
                <option value="INACTIVE">Nonaktif</option>
                <option value="REJECTED">Ditolak</option>
            </select>
            <select wire:model.live="filterType"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                <option value="">Semua Tipe</option>
                <option value="CASH">Cash</option>
                <option value="CREDIT">Credit</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Outlet</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Area</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Tipe</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50/50 transition-colors {{ $customer->trashed() ? 'opacity-50' : '' }}">
                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $customer->customer_code }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-900">{{ $customer->customer_name }}</p>
                            @if($customer->credit_limit)
                            <p class="text-xs text-slate-400">Limit: Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $customer->area->area_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ $customer->customer_type === 'CREDIT' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $customer->customer_type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColor = match($customer->status) {
                                    'ACTIVE'           => 'bg-emerald-100 text-emerald-700',
                                    'PENDING_APPROVAL' => 'bg-amber-100 text-amber-700',
                                    'REJECTED'         => 'bg-red-100 text-red-700',
                                    default            => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusColor }}">{{ $customer->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if(!$customer->trashed())
                            <button wire:click="openEdit({{ $customer->id }})"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                                Edit
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">
                            Belum ada customer.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    {{-- Leaflet map scripts (tidak berubah) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let outletMap = null, outletMarker = null, outletCircle = null;
        function initMap() {
            const mapEl = document.getElementById("outlet-map");
            if (!mapEl) return;
            if (outletMap) { outletMap.remove(); outletMap = outletMarker = outletCircle = null; }
            const latVal = parseFloat(document.getElementById("map-lat")?.value);
            const lngVal = parseFloat(document.getElementById("map-lng")?.value);
            const hasCoord = !isNaN(latVal) && !isNaN(lngVal);
            outletMap = L.map("outlet-map").setView(hasCoord ? [latVal, lngVal] : [-6.2088, 106.8456], hasCoord ? 17 : 12);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { attribution: "© OpenStreetMap", maxZoom: 19 }).addTo(outletMap);
            if (hasCoord) placeMarker(latVal, lngVal);
            outletMap.on("click", e => { placeMarker(e.latlng.lat, e.latlng.lng); updateInputs(e.latlng.lat, e.latlng.lng); });
        }
        function placeMarker(lat, lng) {
            if (outletMarker) outletMap.removeLayer(outletMarker);
            if (outletCircle) outletMap.removeLayer(outletCircle);
            outletMarker = L.marker([lat, lng], { draggable: true }).addTo(outletMap).bindPopup("📍 Lokasi Outlet").openPopup();
            outletMarker.on("dragend", e => { const p = e.target.getLatLng(); updateInputs(p.lat, p.lng); drawRadius(p.lat, p.lng); });
            drawRadius(lat, lng);
        }
        function drawRadius(lat, lng) {
            if (outletCircle) outletMap.removeLayer(outletCircle);
            const radius = parseInt(document.getElementById("map-radius")?.value) || 100;
            outletCircle = L.circle([lat, lng], { radius, color: "#f59e0b", fillColor: "#f59e0b", fillOpacity: 0.1, weight: 2 }).addTo(outletMap);
        }
        function updateInputs(lat, lng) {
            ["map-lat", "map-lng"].forEach((id, i) => {
                const el = document.getElementById(id);
                if (el) { el.value = (i === 0 ? lat : lng).toFixed(7); el.dispatchEvent(new Event("input")); }
            });
        }
        function useMyLocation() {
            if (!navigator.geolocation) return alert("Browser tidak mendukung geolocation.");
            navigator.geolocation.getCurrentPosition(p => {
                updateInputs(p.coords.latitude, p.coords.longitude);
                if (outletMap) { outletMap.setView([p.coords.latitude, p.coords.longitude], 17); placeMarker(p.coords.latitude, p.coords.longitude); }
            }, () => alert("Gagal mendapatkan lokasi."));
        }
        function resetMap() {
            if (outletMarker) outletMap.removeLayer(outletMarker);
            if (outletCircle) outletMap.removeLayer(outletCircle);
            outletMarker = outletCircle = null;
            updateInputs("", "");
            outletMap.setView([-6.2088, 106.8456], 12);
        }
        document.addEventListener("DOMContentLoaded", () => setTimeout(initMap, 200));
        document.addEventListener("livewire:updated", () => setTimeout(initMap, 200));
        window.addEventListener("formOpened", () => {
            let attempts = 0;
            const iv = setInterval(() => { if (document.getElementById("outlet-map")) { clearInterval(iv); initMap(); } else if (++attempts >= 10) clearInterval(iv); }, 200);
        });
        document.addEventListener("input", e => { if (e.target.id === "map-radius" && outletMarker) { const p = outletMarker.getLatLng(); drawRadius(p.lat, p.lng); } });
    </script>
</div>
