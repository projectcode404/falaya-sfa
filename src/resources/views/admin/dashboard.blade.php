<x-layouts.app heading="Dashboard">

    {{-- Blocking banner --}}
    @if(!$isSynced)
    <div class="mb-6 flex items-center gap-3 rounded-lg border border-orange-300 bg-orange-50 px-4 py-3">
        <x-heroicon-o-exclamation-triangle class="h-5 w-5 flex-shrink-0 text-orange-500"/>
        <div class="flex-1 text-sm text-orange-800">
            <strong class="font-semibold">Stock Loading diblokir.</strong>
            Hari operasional belum ditutup. Selesaikan Closing Harian terlebih dahulu.
        </div>
        <a href="/admin/closing" class="flex-shrink-0 rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-700 transition-colors">
            Tutup Sekarang →
        </a>
    </div>
    @endif

    {{-- Pipeline card --}}
    <div class="mb-6 rounded-xl bg-slate-900 px-6 py-5">
        <div class="mb-5 flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">Pipeline Hari Ini</span>
            <span class="text-xs text-slate-500">{{ $today->translatedFormat('l, d F Y') }}</span>
        </div>

        @php
            $totalSalesman = count($salesmen);
            $loadingDone   = collect($salesmen)->where('loading', true)->count();
            $visitTotal    = collect($salesmen)->sum(fn($s) => (int) explode('/', $s['visit'])[1]);
            $visitDone     = collect($salesmen)->sum(fn($s) => (int) explode('/', $s['visit'])[0]);
            $unloadingDone = collect($salesmen)->where('unloading', true)->count();
            $reconDone     = collect($salesmen)->where('cash_recon', true)->count();

            $steps = [
                ['label' => 'Loading',       'value' => $loadingDone,   'total' => $totalSalesman, 'sub' => "dari {$totalSalesman} salesman", 'color' => 'bg-emerald-500'],
                ['label' => 'Visit',          'value' => $visitDone,     'total' => $visitTotal,    'sub' => "dari {$visitTotal} rencana",     'color' => 'bg-amber-500'],
                ['label' => 'Unloading',      'value' => $unloadingDone, 'total' => $totalSalesman, 'sub' => "dari {$totalSalesman} salesman", 'color' => 'bg-emerald-500'],
                ['label' => 'Rekonsiliasi',   'value' => $reconDone,     'total' => $totalSalesman, 'sub' => "dari {$totalSalesman} salesman", 'color' => 'bg-blue-500'],
            ];
        @endphp

        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach($steps as $step)
            @php $pct = $step['total'] > 0 ? round($step['value'] / $step['total'] * 100) : 0; @endphp
            <div>
                <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-widest text-slate-500">{{ $step['label'] }}</p>
                <div class="mb-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-700">
                    <div class="h-full rounded-full {{ $step['color'] }} transition-all" style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-xl font-bold leading-none text-white">{{ $step['value'] }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $step['sub'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Salesman chips --}}
        @if(count($salesmen) > 0)
        <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-800 pt-4">
            @foreach($salesmen as $s)
            @php
                $allDone = $s['loading'] && $s['visit_done'] && $s['unloading'] && $s['cash_recon'];
                $inProgress = $s['loading'] && !$allDone;
                $dotColor = $allDone ? 'bg-emerald-500' : ($inProgress ? 'bg-amber-500' : 'bg-slate-600');
            @endphp
            <div class="flex items-center gap-1.5 rounded-md bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-300">
                <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                {{ $s['name'] }}
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Quick actions --}}
    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4">
        <a href="/admin/stock-loading" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition-all hover:border-amber-300 hover:shadow-sm">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-amber-100">
                <x-heroicon-o-arrow-up-tray class="h-4 w-4 text-amber-700"/>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-900">Stock Loading</p>
                <p class="text-xs text-slate-500">Serahkan stok ke salesman</p>
            </div>
        </a>
        <a href="/admin/stock-unloading" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition-all hover:border-amber-300 hover:shadow-sm">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-100">
                <x-heroicon-o-arrow-down-tray class="h-4 w-4 text-emerald-700"/>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-900">Stock Unloading</p>
                <p class="text-xs text-slate-500">Terima stok dari salesman</p>
            </div>
        </a>
        <a href="/admin/payment-transfer" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition-all hover:border-amber-300 hover:shadow-sm">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100">
                <x-heroicon-o-credit-card class="h-4 w-4 text-blue-700"/>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-900">Payment Transfer</p>
                <p class="text-xs text-slate-500">Input pembayaran bank</p>
            </div>
        </a>
        <a href="/admin/cash-reconciliation" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition-all hover:border-amber-300 hover:shadow-sm">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-slate-100">
                <x-heroicon-o-arrows-right-left class="h-4 w-4 text-slate-600"/>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-900">Rekonsiliasi Cash</p>
                <p class="text-xs text-slate-500">Cocokkan setoran salesman</p>
            </div>
        </a>
    </div>

    {{-- Progress salesman table --}}
    <div class="rounded-xl border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Progress Salesman</h2>
            <a href="/admin/closing" class="text-xs font-semibold text-amber-500 hover:text-amber-600">Closing Harian →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Salesman</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Loading</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Visit</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Unloading</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Rekon</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($salesmen as $s)
                    @php
                        [$vDone, $vTotal] = explode('/', $s['visit']);
                        $vPct = $vTotal > 0 ? round((int)$vDone / (int)$vTotal * 100) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3">
                            <p class="font-semibold text-slate-900">{{ $s['name'] }}</p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($s['loading'])
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <x-heroicon-m-check class="h-3 w-3"/>
                                </span>
                            @else
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-slate-400">–</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $vPct }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-700">{{ $s['visit'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($s['unloading'])
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <x-heroicon-m-check class="h-3 w-3"/>
                                </span>
                            @else
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-slate-400">–</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($s['cash_recon'])
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <x-heroicon-m-check class="h-3 w-3"/>
                                </span>
                            @else
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-slate-400">–</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">
                            Belum ada salesman aktif hari ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
