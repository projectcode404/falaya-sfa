<x-layouts.app heading="Bad Stock Summary">

    <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Dari</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Sampai</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-5">

        {{-- Masuk bad stock --}}
        <div class="lg:col-span-3 rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Masuk Bad Stock — Periode Ini</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Produk</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Adjustment</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Return</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Est. Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($summary as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $row['product'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ number_format($row['adjustment'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ number_format($row['return'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center font-bold text-red-500">{{ number_format($row['total'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">Rp {{ number_format($row['nilai'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada bad stock masuk periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Saldo bad stock --}}
        <div class="lg:col-span-2 rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Saldo Bad Stock Gudang Saat Ini</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Produk</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Qty BAD</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($currentBad as $b)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $b->product->product_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-center font-bold text-red-500">{{ number_format($b->qty, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada bad stock.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</x-layouts.app>
