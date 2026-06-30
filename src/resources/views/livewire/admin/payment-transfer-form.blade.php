<div>
    {{-- Header --}}
    <div class="mb-5">
        <h2 class="text-sm font-semibold text-slate-900">Payment Transfer</h2>
        <p class="text-xs text-slate-400">Verifikasi mutasi bank terlebih dahulu sebelum input pembayaran.</p>
    </div>

    {{-- Flash --}}
    @if($submitSuccess)
    <div class="mb-5 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <x-heroicon-o-check-circle class="h-4 w-4 flex-shrink-0 text-emerald-500"/>
        {{ $submitSuccess }}
    </div>
    @endif
    @if($submitError)
    <div class="mb-5 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <x-heroicon-o-x-circle class="h-4 w-4 flex-shrink-0 text-red-500"/>
        {{ $submitError }}
    </div>
    @endif

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        {{-- Customer list --}}
        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-3">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"/>
                    <input type="text" wire:model.live.debounce.400ms="search"
                           placeholder="Cari customer CREDIT..."
                           class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                </div>
            </div>
            <div class="max-h-96 divide-y divide-slate-50 overflow-y-auto">
                @forelse($customers as $c)
                <button type="button" wire:click="selectCustomer({{ $c['id'] }})"
                        class="flex w-full flex-col px-5 py-3 text-left transition-colors
                               {{ $selectedCustomerId === $c['id'] ? 'bg-amber-50' : 'hover:bg-slate-50' }}">
                    <span class="text-sm font-semibold {{ $selectedCustomerId === $c['id'] ? 'text-amber-800' : 'text-slate-900' }}">
                        {{ $c['customer_name'] }}
                    </span>
                    <span class="text-xs text-slate-400">{{ $c['customer_code'] }}</span>
                </button>
                @empty
                <div class="px-5 py-8 text-center text-sm text-slate-400">Tidak ada customer ditemukan.</div>
                @endforelse
            </div>
        </div>

        {{-- Invoice & payment form --}}
        <div class="lg:col-span-2">
            @if($selectedCustomerId)
            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-900">{{ $selectedCustomerName }}</h3>
                </div>
                <div class="px-5 py-4">
                    @if(count($invoices) === 0)
                    <div class="flex items-center gap-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                        <x-heroicon-o-information-circle class="h-4 w-4 flex-shrink-0"/>
                        Tidak ada invoice outstanding untuk customer ini.
                    </div>
                    @else
                    <div class="mb-5 overflow-hidden rounded-xl border border-slate-200">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500" style="width:40px"></th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Invoice</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Jatuh Tempo</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Jumlah Bayar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($invoices as $inv)
                                <tr wire:key="inv-{{ $inv['id'] }}">
                                    <td class="px-4 py-3">
                                        <input type="checkbox"
                                               wire:click="toggleSelect({{ $inv['id'] }})"
                                               {{ ($selected[$inv['id']] ?? false) ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-slate-300 accent-amber-500">
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $inv['invoice_number'] }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-600">{{ $inv['due_date'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                                     {{ $inv['status'] === 'OVERDUE' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $inv['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($selected[$inv['id']] ?? false)
                                        <input type="number" wire:model.lazy="amounts.{{ $inv['id'] }}"
                                               min="0" max="{{ $inv['remaining_amount'] }}" step="1000"
                                               class="w-32 rounded-lg border border-slate-200 px-3 py-1.5 text-right text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                                        @else
                                        <span class="text-sm text-slate-400">Rp {{ number_format($inv['remaining_amount'], 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-4 flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3">
                        <span class="text-sm font-semibold text-slate-700">Total Transfer</span>
                        <span class="text-lg font-bold text-slate-900">Rp {{ number_format($this->getTotal(), 0, ',', '.') }}</span>
                    </div>

                    <div class="mb-5">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Catatan <span class="font-normal text-slate-400">(no. referensi transfer, dll)</span></label>
                        <input type="text" wire:model="notes" placeholder="Ref BCA #123456"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    </div>

                    <button wire:click="submitTransfer" wire:loading.attr="disabled"
                            class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800 disabled:opacity-50">
                        <span wire:loading wire:target="submitTransfer">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        </span>
                        <span wire:loading.remove wire:target="submitTransfer">Simpan Payment Transfer</span>
                        <span wire:loading wire:target="submitTransfer">Memproses...</span>
                    </button>
                    @endif
                </div>
            </div>
            @else
            <div class="flex h-full items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white px-5 py-16 text-center">
                <div>
                    <x-heroicon-o-credit-card class="mx-auto mb-2 h-8 w-8 text-slate-300"/>
                    <p class="text-sm text-slate-400">Cari dan pilih customer untuk mencatat payment transfer.</p>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
