<x-layouts.app heading="Dashboard Admin">
    @if(!$isSynced)
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div class="flex-grow-1">
                <strong>Hari operasional belum ditutup.</strong>
                Stock Loading tidak dapat dibuat sampai Closing Harian selesai.
            </div>
            <a href="/admin/closing" class="btn btn-danger btn-sm ms-3">Tutup Sekarang →</a>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Progress Hari Ini — {{ $today->translatedFormat('l, d F Y') }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Salesman</th>
                        <th class="text-center">Loading</th>
                        <th class="text-center">Visit</th>
                        <th class="text-center">Unloading</th>
                        <th class="text-center">Cash Recon</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesmen as $s)
                        <tr>
                            <td>{{ $s['name'] }}</td>
                            <td class="text-center">
                                @if($s['loading'])
                                    <span class="badge bg-success-lt">✅ Selesai</span>
                                @else
                                    <span class="badge bg-secondary-lt">⏳ Belum</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s['visit_done'])
                                    <span class="badge bg-success-lt">✅ {{ $s['visit'] }}</span>
                                @else
                                    <span class="badge bg-warning-lt">⏳ {{ $s['visit'] }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s['unloading'])
                                    <span class="badge bg-success-lt">✅ Selesai</span>
                                @else
                                    <span class="badge bg-secondary-lt">⏳ Belum</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s['cash_recon'])
                                    <span class="badge bg-success-lt">✅ Selesai</span>
                                @else
                                    <span class="badge bg-secondary-lt">⏳ Belum</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada salesman aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <a href="/admin/closing" class="btn btn-primary">Proses Closing Harian →</a>
        </div>
    </div>
</x-layouts.app>
