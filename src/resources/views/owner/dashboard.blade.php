<x-layouts.app heading="Dashboard">

    {{-- Stat cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Penjualan Hari Ini</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Sales Order POSTED hari ini</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Outstanding Piutang</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">Rp {{ number_format($outstandingPiutang, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Total invoice belum lunas</p>
        </div>

        <div class="rounded-xl border bg-white p-5 {{ $invoiceOverdue > 0 ? 'border-red-300' : 'border-slate-200' }}">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Invoice Overdue</p>
            <p class="mt-2 text-2xl font-bold {{ $invoiceOverdue > 0 ? 'text-red-500' : 'text-slate-900' }}">{{ $invoiceOverdue }}</p>
            <p class="mt-1 text-xs text-slate-400">Invoice melewati jatuh tempo</p>
        </div>

        <div class="rounded-xl border bg-white p-5 {{ $approvalPending > 0 ? 'border-amber-300' : 'border-slate-200' }}">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Approval Pending</p>
            <p class="mt-2 text-2xl font-bold {{ $approvalPending > 0 ? 'text-amber-500' : 'text-slate-900' }}">{{ $approvalPending }}</p>
            <a href="/owner/approvals" class="mt-1 block text-xs font-semibold text-amber-500 hover:text-amber-600">Lihat semua →</a>
        </div>

    </div>

    {{-- Main grid --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Performa Salesman --}}
        <div class="lg:col-span-2 rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Performa Salesman — Hari Ini</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Salesman</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Compliance</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Omzet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($salesmen as $s)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $s['name'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $s['compliance'] >= 80 ? 'bg-emerald-500' : 'bg-amber-500' }}"
                                             style="width: {{ $s['compliance'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-700">{{ $s['compliance'] }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">
                                Rp {{ number_format($s['omzet'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-sm text-slate-400">
                                Belum ada data salesman aktif.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right column --}}
        <div class="flex flex-col gap-5">

            {{-- Stok Gudang --}}
            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Stok Gudang</h2>
                    <a href="/reports/stock" class="text-xs font-semibold text-amber-500 hover:text-amber-600">Detail →</a>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="text-sm text-slate-600">GOOD</span>
                        </div>
                        <span class="text-sm font-bold text-emerald-600">{{ number_format($gudangGood, 0, ',', '.') }} pcs</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full {{ $gudangBad > 0 ? 'bg-red-500' : 'bg-slate-300' }}"></span>
                            <span class="text-sm text-slate-600">BAD</span>
                        </div>
                        <span class="text-sm font-bold {{ $gudangBad > 0 ? 'text-red-500' : 'text-slate-400' }}">
                            {{ number_format($gudangBad, 0, ',', '.') }} pcs
                        </span>
                    </div>
                </div>
            </div>

            {{-- Approval terbaru --}}
            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Menunggu Approval</h2>
                    <a href="/owner/approvals" class="text-xs font-semibold text-amber-500 hover:text-amber-600">Semua →</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($approvalTerbaru as $item)
                    <a href="{{ $item['url'] }}" class="flex items-start gap-3 px-5 py-3 hover:bg-slate-50 transition-colors">
                        <div class="mt-0.5 flex-shrink-0">
                            @if($item['type'] === 'Customer Baru')
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100">
                                    <x-heroicon-o-building-storefront class="h-4 w-4 text-amber-700"/>
                                </div>
                            @elseif($item['type'] === 'Stock Adjustment')
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100">
                                    <x-heroicon-o-shield-exclamation class="h-4 w-4 text-red-700"/>
                                </div>
                            @else
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100">
                                    <x-heroicon-o-credit-card class="h-4 w-4 text-blue-700"/>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-900">{{ $item['type'] }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $item['label'] }}</p>
                            <p class="text-xs text-slate-400">{{ $item['by'] }}</p>
                        </div>
                        <span class="flex-shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">Pending</span>
                    </a>
                    @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-400">
                        Tidak ada pengajuan pending.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>
