<x-layouts.app heading="Laporan Penjualan">
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
                            <option value="{{ $s->id }}" {{ request('salesman_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h3 class="card-title">Hasil — {{ $rows->total() }} transaksi</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. SO</th>
                        <th>Tanggal</th>
                        <th>Salesman</th>
                        <th>Customer</th>
                        <th>Tipe</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $so)
                        <tr>
                            <td><span class="text-muted font-monospace small">{{ $so->document_number }}</span></td>
                            <td>{{ $so->operational_date }}</td>
                            <td>{{ $so->salesman->name ?? '-' }}</td>
                            <td>{{ $so->customer->customer_name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $so->payment_type === 'CASH' ? 'bg-green-lt' : 'bg-blue-lt' }}">
                                    {{ $so->payment_type }}
                                </span>
                            </td>
                            <td class="text-end">Rp {{ number_format($so->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data untuk filter ini.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->count() > 0)
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="5">Total</td>
                            <td class="text-end">Rp {{ number_format($rows->sum('total_amount'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        <div class="card-footer">{{ $rows->withQueryString()->links() }}</div>
    </div>
</x-layouts.app>
