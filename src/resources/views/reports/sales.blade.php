<x-layouts.app heading="Laporan Penjualan">

    {{-- Filter --}}
    <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
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
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Salesman</label>
                <select name="salesman_id"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    <option value="">Semua</option>
                    @foreach($salesmen as $s)
                    <option value="{{ $s->id }}" {{ request('salesman_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Hasil — {{ $rows->total() }} transaksi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">No. SO</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Salesman</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Tipe</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($rows as $so)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $so->document_number }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $so->operational_date }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $so->salesman->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $so->customer->customer_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                         {{ $so->payment_type === 'CASH' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $so->payment_type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">Rp {{ number_format($so->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada data untuk filter ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rows->count() > 0)
                <tfoot>
                    <tr class="border-t border-slate-200 bg-slate-50">
                        <td colspan="5" class="px-5 py-3 text-sm font-bold text-slate-900">Total</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-slate-900">Rp {{ number_format($rows->sum('total_amount'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($rows->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">{{ $rows->withQueryString()->links() }}</div>
        @endif
    </div>

</x-layouts.app>
