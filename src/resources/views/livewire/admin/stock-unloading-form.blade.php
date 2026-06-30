<div>
    {{-- Header --}}
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Stock Unloading</h2>
            <p class="text-xs text-slate-400">Terima kembali stok dari salesman</p>
        </div>
        <button wire:click="openCreate"
                class="flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-slate-800">
            <x-heroicon-m-plus class="h-4 w-4"/>
            Proses Unloading
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <x-heroicon-o-check-circle class="h-4 w-4 flex-shrink-0 text-emerald-500"/>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <x-heroicon-o-x-circle class="h-4 w-4 flex-shrink-0 text-red-500"/>
        {{ session('error') }}
    </div>
    @endif

    {{-- Form --}}
    @if($showForm)
    <div class="mb-5 rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-sm font-semibold text-slate-900">Proses Stock Unloading</h3>
        </div>
        <div class="px-5 py-4">
            <div class="mb-4 max-w-xs">
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Salesman <span class="text-red-500">*</span></label>
                <select wire:model.live="salesman_id"
                        class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                               {{ $errors->has('salesman_id') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                    <option value="">-- Pilih Salesman --</option>
                    @foreach($salesmen as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('salesman_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @if($salesman_id && count($items) > 0)
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Produk</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Stok Dibawa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500" style="width:160px">Qty Kembali</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($items as $index => $item)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $item['product_name'] }}</p>
                                <p class="text-xs text-slate-400">{{ $item['unit'] }}</p>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-slate-900">{{ number_format($item['max_qty'], 0) }}</td>
                            <td class="px-4 py-3">
                                <input type="number" wire:model="items.{{ $index }}.qty"
                                       min="0" max="{{ $item['max_qty'] }}" step="1"
                                       class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @elseif($salesman_id)
            <div class="flex items-center gap-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                <x-heroicon-o-information-circle class="h-4 w-4 flex-shrink-0"/>
                Salesman ini tidak memiliki stok bawaan hari ini.
            </div>
            @else
            <div class="rounded-xl border border-dashed border-slate-200 px-5 py-8 text-center">
                <p class="text-sm text-slate-400">Pilih salesman terlebih dahulu.</p>
            </div>
            @endif
        </div>
        <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
            <button wire:click="save" wire:loading.attr="disabled"
                    class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-slate-800 disabled:opacity-50">
                <span wire:loading wire:target="save">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                </span>
                Post Unloading
            </button>
            <button wire:click="cancelForm"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                Batal
            </button>
        </div>
    </div>
    @endif

    {{-- List --}}
    <div class="rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 px-5 py-3">
            <select wire:model.live="filterSalesman"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                <option value="">Semua Salesman</option>
                @foreach($salesmen as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">No. Dokumen</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Salesman</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tgl Operasional</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Item</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($unloadings as $unloading)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $unloading->document_number }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $unloading->salesman->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $unloading->operational_date }}</td>
                        <td class="px-4 py-3">
                            @foreach($unloading->items as $item)
                            <p class="text-xs text-slate-600">{{ $item->product->product_name ?? '-' }}: {{ number_format($item->qty, 0) }}</p>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($unloading->status === 'POSTED')
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">POSTED</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">DRAFT</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada stock unloading.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($unloadings->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">{{ $unloadings->links() }}</div>
        @endif
    </div>
</div>
