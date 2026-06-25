<x-layouts.app heading="Bad Stock Summary">
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

    <div class="row row-cards mb-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Masuk Bad Stock — Periode Ini</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Dari Adjustment</th>
                                <th class="text-center">Dari Return</th>
                                <th class="text-center">Total</th>
                                <th class="text-end">Est. Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summary as $row)
                                <tr>
                                    <td>{{ $row['product'] }}</td>
                                    <td class="text-center">{{ number_format($row['adjustment'], 0, ',', '.') }}</td>
                                    <td class="text-center">{{ number_format($row['return'], 0, ',', '.') }}</td>
                                    <td class="text-center fw-bold text-danger">{{ number_format($row['total'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['nilai'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada bad stock masuk periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Saldo Bad Stock Gudang Saat Ini</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center text-danger">Qty BAD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($currentBad as $b)
                                <tr>
                                    <td>{{ $b->product->product_name ?? '-' }}</td>
                                    <td class="text-center text-danger fw-bold">{{ number_format($b->qty, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">Tidak ada bad stock.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
