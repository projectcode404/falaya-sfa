<x-layouts.app heading="Collection Risk">
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
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Customer dengan Frekuensi Skip Tinggi</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Salesman</th>
                        <th class="text-center">Jumlah Skip</th>
                        <th>Terakhir Skip</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r->customer->customer_name ?? '-' }}</td>
                            <td>{{ $r->salesman->name ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $r->total_skip >= 3 ? 'bg-danger' : 'bg-warning-lt' }}">
                                    {{ $r->total_skip }}x
                                </span>
                            </td>
                            <td>{{ $r->last_skip }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data collection risk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $rows->withQueryString()->links() }}</div>
    </div>
</x-layouts.app>
