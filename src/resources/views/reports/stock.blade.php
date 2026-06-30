<x-layouts.app heading="Laporan Stok">

    <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Holder</label>
                <select name="holder_type"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    <option value="">Semua</option>
                    <option value="WAREHOUSE" {{ request('holder_type') === 'WAREHOUSE' ? 'selected' : '' }}>Gudang</option>
                    <option value="SALESMAN" {{ request('holder_type') === 'SALESMAN' ? 'selected' : '' }}>Salesman</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kondisi</label>
                <select name="condition"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    <option value="">Semua</option>
                    <option value="GOOD" {{ request('condition') === 'GOOD' ? 'selected' : '' }}>GOOD</option>
                    <option value="BAD" {{ request('condition') === 'BAD' ? 'selected' : '' }}>BAD</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Produk</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">GOOD (Gudang)</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">BAD (Gudang)</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">GOOD (Salesman)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($balances as $productId => $rows)
                    @php
                        $product = $rows->first()->product;
                        $wGood = $rows->where('holder_type', 'WAREHOUSE')->where('condition', 'GOOD')->sum('qty');
                        $wBad  = $rows->where('holder_type', 'WAREHOUSE')->where('condition', 'BAD')->sum('qty');
                        $sGood = $rows->where('holder_type', 'SALESMAN')->where('condition', 'GOOD')->sum('qty');
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3 font-semibold text-slate-900">{{ $product->product_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-emerald-600">{{ number_format($wGood, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center font-semibold {{ $wBad > 0 ? 'text-red-500' : 'text-slate-400' }}">{{ number_format($wBad, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ number_format($sGood, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada data stok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
