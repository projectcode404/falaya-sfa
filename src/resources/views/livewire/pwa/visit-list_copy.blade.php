<div>
    <div class="pwa-header">
        <h5>📍 Kunjungan Hari Ini</h5>
        <small>{{ $today->translatedFormat('l, d F Y') }}</small>
    </div>
    <div class="px-3">
        @forelse($visits as $visit)
            @php
                $statusIcon = match($visit->status) {
                    'COMPLETED','NO_ORDER','OUTLET_CLOSED' => '✅',
                    'IN_PROGRESS' => '🔄',
                    'SKIPPED' => '⏭️',
                    default => '⏳',
                };
                $statusLabel = match($visit->status) {
                    'COMPLETED' => 'Selesai',
                    'NO_ORDER' => 'Tidak ada order',
                    'OUTLET_CLOSED' => 'Outlet tutup',
                    'IN_PROGRESS' => 'Sedang dikunjungi',
                    'PLANNED' => 'Belum dikunjungi',
                    'SKIPPED' => 'Dilewati',
                    default => $visit->status,
                };
                $omzet = $visit->salesOrders->sum('total_amount');
            @endphp
            <a href="/pwa/visits/{{ $visit->id }}" class="text-decoration-none text-dark">
                <div class="falaya-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="falaya-card__title">{{ $statusIcon }} {{ $visit->customer->customer_name }}</div>
                            <div class="falaya-card__subtitle">
                                {{ $statusLabel }}
                                @if($omzet > 0) · Rp {{ number_format($omzet, 0, ',', '.') }} @endif
                            </div>
                        </div>
                        @if($visit->status === 'COMPLETED')
                            <span class="badge badge-status-completed">Selesai</span>
                        @elseif($visit->status === 'NO_ORDER')
                            <span class="badge badge-status-no_order">No Order</span>
                        @elseif($visit->status === 'OUTLET_CLOSED')
                            <span class="badge badge-status-outlet_closed">Tutup</span>
                        @elseif($visit->status === 'IN_PROGRESS')
                            <span class="badge badge-status-in_progress">Proses</span>
                        @else
                            <span class="badge badge-status-planned">Belum</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="falaya-card">
                <div class="card-body text-center py-4">
                    <div style="font-size:2rem">📭</div>
                    <div class="falaya-card__subtitle mt-2">Belum ada kunjungan terjadwal hari ini.</div>
                </div>
            </div>
        @endforelse
    </div>
</div>
