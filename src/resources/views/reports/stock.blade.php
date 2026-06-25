<x-layouts.app heading="Laporan Stok">
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-sm-3">
                    <label class="form-label">Holder</label>
                    <select name="holder_type" class="form-select">
                        <option value="">Semua</option>
                        <option value="WAREHOUSE" {{ request('holder_type') === 'WAREHOUSE' ? 'selected' : '' }}>Gudang</option>
                        <option value="SALESMAN" {{ request('holder_type') === 'SALESMAN' ? 'selected' : '' }}>Salesman</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Kondisi</label>
                    <select name="condition" class="form-select">
                        <option value="">Semua</option>
                        <option value="GOOD" {{ request('condition') === 'GOOD' ? 'selected' : '' }}>GOOD</option>
                        <option value="BAD" {{ request('condition') === 'BAD' ? 'selected' : '' }}>BAD</option>
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
                        <th>Produk</th>
                        <th class="text-center">GOOD (Gudang)</th>
                        <th class="text-center">BAD (Gudang)</th>
                        <th class="text-center">GOOD (Salesman)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balances as $productId => $rows)
                        @php
                            $product = $rows->first()->product;
                            $wGood = $rows->where('holder_type','WAREHOUSE')->where('condition','GOOD')->sum('qty');
                            $wBad  = $rows->where('holder_type','WAREHOUSE')->where('condition','BAD')->sum('qty');
                            $sGood = $rows->where('holder_type','SALESMAN')->where('condition','GOOD')->sum('qty');
                        @endphp
                        <tr>
                            <td>{{ $product->product_name ?? '-' }}</td>
                            <td class="text-center text-success">{{ number_format($wGood, 0, ',', '.') }}</td>
                            <td class="text-center {{ $wBad > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($wBad, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($sGood, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
