<x-layouts.app heading="Laporan Kunjungan">
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-sm-3">
                    <label class="form-label">Dari</label>
                    <input type="date" name="from" class="form-control" value="{{ $from->toDateString() }}">
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="to" class="form-control" value="{{ $to->toDateString() }}">
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Salesman</label>
                    <select name="salesman_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach($salesmen as $s)
                            <option value="{{ $s->id }}" {{ request('salesman_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3">
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
                        <th>Tanggal</th>
                        <th>Salesman</th>
                        <th>Customer</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusColor = [
                            'COMPLETED'     => 'bg-success-lt',
                            'NO_ORDER'      => 'bg-info-lt',
                            'OUTLET_CLOSED' => 'bg-secondary-lt',
                            'SKIPPED'       => 'bg-danger-lt',
                            'PLANNED'       => 'bg-warning-lt',
                            'IN_PROGRESS'   => 'bg-blue-lt',
                        ];
                    @endphp
                    @forelse($rows as $v)
                        <tr>
                            <td>{{ $v->operational_date }}</td>
                            <td>{{ $v->salesman->name ?? '-' }}</td>
                            <td>{{ $v->customer->customer_name ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $statusColor[$v->status] ?? 'bg-secondary-lt' }}">
                                    {{ $v->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data kunjungan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $rows->withQueryString()->links() }}</div>
    </div>
</x-layouts.app>
