<x-layouts.app heading="Laporan Kunjungan">

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
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Salesman</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @php
                        $statusColor = [
                            'COMPLETED'     => 'bg-emerald-100 text-emerald-700',
                            'NO_ORDER'      => 'bg-blue-100 text-blue-700',
                            'OUTLET_CLOSED' => 'bg-slate-100 text-slate-500',
                            'SKIPPED'       => 'bg-red-100 text-red-700',
                            'PLANNED'       => 'bg-amber-100 text-amber-700',
                            'IN_PROGRESS'   => 'bg-blue-100 text-blue-700',
                        ];
                    @endphp
                    @forelse($rows as $v)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3 text-slate-600">{{ $v->operational_date }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $v->salesman->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $v->customer->customer_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusColor[$v->status] ?? 'bg-slate-100 text-slate-500' }}">
                                {{ $v->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada data kunjungan.</td>
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
