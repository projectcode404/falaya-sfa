<x-layouts.app heading="Collection Risk">

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

    <div class="rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Customer dengan Frekuensi Skip Tinggi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Salesman</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Jumlah Skip</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Terakhir Skip</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($rows as $r)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3 font-semibold text-slate-900">{{ $r->customer->customer_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $r->salesman->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold
                                         {{ $r->total_skip >= 3 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $r->total_skip }}x
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $r->last_skip }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada data collection risk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">{{ $rows->withQueryString()->links() }}</div>
        @endif
    </div>

</x-layouts.app>
