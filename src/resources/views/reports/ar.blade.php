<x-layouts.app heading="AR Outstanding">

    <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Status</label>
                <select name="status"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    <option value="">Semua</option>
                    <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="PARTIAL" {{ request('status') === 'PARTIAL' ? 'selected' : '' }}>Sebagian</option>
                    <option value="OVERDUE" {{ request('status') === 'OVERDUE' ? 'selected' : '' }}>Overdue</option>
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
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">No. Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($rows as $inv)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $inv->invoice_number }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $inv->customer->customer_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $inv->due_date }}
                            @if($inv->status === 'OVERDUE')
                            <span class="ml-1 text-xs text-red-500">({{ \Carbon\Carbon::parse($inv->due_date)->diffForHumans() }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $sc = ['UNPAID' => 'bg-amber-100 text-amber-700', 'PARTIAL' => 'bg-blue-100 text-blue-700', 'OVERDUE' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $sc[$inv->status] ?? 'bg-slate-100 text-slate-500' }}">{{ $inv->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada outstanding.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rows->count() > 0)
                <tfoot>
                    <tr class="border-t border-slate-200 bg-slate-50">
                        <td colspan="4" class="px-5 py-3 text-sm font-bold text-slate-900">Total Outstanding</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-slate-900">Rp {{ number_format($rows->sum('remaining_amount'), 0, ',', '.') }}</td>
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
