<div>
    {{-- Header --}}
    <div class="mb-5">
        <h2 class="text-sm font-semibold text-slate-900">Cash Reconciliation</h2>
        <p class="text-xs text-slate-400">Tanggal operasional: {{ $operationalDate }}</p>
    </div>

    {{-- Last result --}}
    @if($lastResult)
    <div class="mb-5 flex items-start gap-3 rounded-lg border px-4 py-3
                {{ $lastResult['status'] === 'RECONCILED' ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }}">
        @if($lastResult['status'] === 'RECONCILED')
            <x-heroicon-o-check-circle class="h-5 w-5 flex-shrink-0 text-emerald-500 mt-0.5"/>
        @else
            <x-heroicon-o-exclamation-triangle class="h-5 w-5 flex-shrink-0 text-red-500 mt-0.5"/>
        @endif
        <div class="text-sm {{ $lastResult['status'] === 'RECONCILED' ? 'text-emerald-800' : 'text-red-800' }}">
            <p class="font-semibold">{{ $lastResult['status'] === 'RECONCILED' ? 'RECONCILED' : 'DISCREPANCY' }}</p>
            <p class="mt-0.5 text-xs">
                Sistem: Rp {{ number_format($lastResult['system_total'], 0, ',', '.') }} ·
                Diterima: Rp {{ number_format($lastResult['actual_received'], 0, ',', '.') }} ·
                Selisih: Rp {{ number_format($lastResult['difference'], 0, ',', '.') }}
            </p>
        </div>
    </div>
    @endif

    @if($submitError)
    <div class="mb-5 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <x-heroicon-o-x-circle class="h-4 w-4 flex-shrink-0 text-red-500"/>
        {{ $submitError }}
    </div>
    @endif

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        {{-- Daftar Salesman --}}
        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="text-sm font-semibold text-slate-900">Daftar Salesman</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($salesmanList as $s)
                <button type="button" wire:click="selectSalesman({{ $s['id'] }})"
                        class="flex w-full items-center justify-between px-5 py-3 text-left transition-colors
                               {{ $selectedSalesmanId === $s['id'] ? 'bg-amber-50' : 'hover:bg-slate-50' }}">
                    <span class="text-sm font-semibold {{ $selectedSalesmanId === $s['id'] ? 'text-amber-800' : 'text-slate-900' }}">
                        {{ $s['name'] }}
                    </span>
                    @php
                        $badgeColor = match($s['status']) {
                            'RECONCILED'  => 'bg-emerald-100 text-emerald-700',
                            'DISCREPANCY' => 'bg-red-100 text-red-700',
                            default       => 'bg-slate-100 text-slate-500',
                        };
                    @endphp
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeColor }}">{{ $s['status'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Form Reconciliation --}}
        <div class="lg:col-span-2">
            @if($selectedSalesmanId)
            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-900">Proses Reconciliation</h3>
                </div>
                <div class="px-5 py-4">
                    {{-- Breakdown --}}
                    <div class="mb-5 overflow-hidden rounded-xl border border-slate-200">
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-slate-50">
                                <tr>
                                    <td class="px-4 py-3 text-slate-600">Cash Sales (Sales Order CASH)</td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-900">Rp {{ number_format($cashSalesTotal, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 text-slate-600">Collection Cash (Payment CASH)</td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-900">Rp {{ number_format($collectionCashTotal, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="bg-slate-50">
                                    <td class="px-4 py-3 font-bold text-slate-900">Total Sistem</td>
                                    <td class="px-4 py-3 text-right text-lg font-bold text-slate-900">Rp {{ number_format($systemTotal, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Uang Diterima Fisik (Rp)</label>
                        <input type="number" wire:model="actualReceived" step="1000" placeholder="0"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    </div>

                    <div class="mb-5">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Catatan <span class="font-normal text-slate-400">(wajib jika ada selisih)</span></label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"></textarea>
                    </div>

                    <button wire:click="submitReconciliation" wire:loading.attr="disabled"
                            class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800 disabled:opacity-50">
                        <span wire:loading wire:target="submitReconciliation">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        </span>
                        <span wire:loading.remove wire:target="submitReconciliation">Proses Reconciliation</span>
                        <span wire:loading wire:target="submitReconciliation">Memproses...</span>
                    </button>
                </div>
            </div>
            @else
            <div class="flex h-full items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white px-5 py-16 text-center">
                <div>
                    <x-heroicon-o-arrows-right-left class="mx-auto mb-2 h-8 w-8 text-slate-300"/>
                    <p class="text-sm text-slate-400">Pilih salesman dari daftar untuk memproses reconciliation.</p>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
