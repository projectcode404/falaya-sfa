<x-layouts.app heading="AR Outstanding">
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>Belum Lunas</option>
                        <option value="PARTIAL" {{ request('status') === 'PARTIAL' ? 'selected' : '' }}>Sebagian</option>
                        <option value="OVERDUE" {{ request('status') === 'OVERDUE' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Customer</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Sisa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $inv)
                        <tr>
                            <td class="font-monospace small">{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->customer->customer_name ?? '-' }}</td>
                            <td>
                                {{ $inv->due_date }}
                                @if($inv->status === 'OVERDUE')
                                    <span class="text-danger small ms-1">({{ \Carbon\Carbon::parse($inv->due_date)->diffForHumans() }})</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $sc = ['UNPAID'=>'bg-warning-lt','PARTIAL'=>'bg-blue-lt','OVERDUE'=>'bg-danger-lt'];
                                @endphp
                                <span class="badge {{ $sc[$inv->status] ?? 'bg-secondary-lt' }}">{{ $inv->status }}</span>
                            </td>
                            <td class="text-end fw-bold">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada outstanding.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->count() > 0)
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="4">Total Outstanding</td>
                            <td class="text-end">Rp {{ number_format($rows->sum('remaining_amount'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        <div class="card-footer">{{ $rows->withQueryString()->links() }}</div>
    </div>
</x-layouts.app>
