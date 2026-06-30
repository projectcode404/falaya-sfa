<div>
    {{-- Header --}}
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Stock Loading</h2>
            <p class="text-xs text-slate-400">Serahkan stok dari gudang ke salesman</p>
        </div>
        <button wire:click="openCreate"
                class="flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-slate-800">
            <x-heroicon-m-plus class="h-4 w-4"/>
            Buat Loading
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
            <h3 class="text-sm font-semibold text-slate-900">Buat Stock Loading Baru</h3>
        </div>
        <div class="px-5 py-4">
            {{-- Pilih Salesman --}}
            <div class="mb-5 max-w-xs">
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

            @if($salesman_id)
            {{-- Search Produk --}}
            <div class="mb-4">
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Tambah Produk</label>
                <div class="relative max-w-md">
                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"/>
                    <input type="text" wire:model.live.debounce.300ms="productSearch"
                           placeholder="Cari nama produk atau varian..."
                           autocomplete="off"
                           class="w-full rounded-lg border border-slate-200 py-2.5 pl-9 pr-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    @if($showSearchResults)
                    <div class="absolute z-10 mt-1 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                        <div class="max-h-64 overflow-y-auto">
                            @foreach($searchResults as $result)
                            <button type="button" wire:click="addProduct({{ $result['product_id'] }})"
                                    class="flex w-full items-center justify-between px-4 py-2.5 text-left hover:bg-slate-50 transition-colors">
                                <div>
                                    <span class="text-sm font-semibold text-slate-900">{{ $result['product_name'] }}</span>
                                    @if($result['variant'])
                                    <span class="text-sm text-slate-400"> — {{ $result['variant'] }}</span>
                                    @endif
                                    <span class="block text-xs text-slate-400">({{ $result['unit'] }})</span>
                                </div>
                                <span class="ml-3 flex-shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold
                                             {{ $result['gudang_qty'] > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    Stok: {{ number_format($result['gudang_qty'], 0) }}
                                </span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if(strlen(trim($productSearch)) >= 2 && !$showSearchResults)
                    <div class="absolute z-10 mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lg">
                        <p class="text-sm text-slate-400">Tidak ada produk ditemukan.</p>
                    </div>
                    @endif
                </div>
                <p class="mt-1 text-xs text-slate-400">Ketik minimal 2 karakter untuk mencari produk.</p>
            </div>

            {{-- Error items --}}
            @error('items')
            <div class="mb-3 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-800">
                <x-heroicon-o-exclamation-triangle class="h-4 w-4 flex-shrink-0"/>
                {{ $message }}
            </div>
            @enderror

            {{-- Items table --}}
            @if(count($items) > 0)
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Produk</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Stok Gudang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500" style="width:160px">Qty Loading</th>
                            <th class="px-4 py-3" style="width:48px"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($items as $index => $item)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $item['product_name'] }}</p>
                                @if($item['variant'])
                                <p class="text-xs text-slate-400">{{ $item['variant'] }} · {{ $item['unit'] }}</p>
                                @else
                                <p class="text-xs text-slate-400">{{ $item['unit'] }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-bold {{ $item['gudang_qty'] <= 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                    {{ number_format($item['gudang_qty'], 0) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" wire:model="items.{{ $index }}.qty"
                                       min="0" max="{{ $item['gudang_qty'] }}" step="1"
                                       {{ $item['gudang_qty'] <= 0 ? 'disabled' : '' }}
                                       class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2
                                              {{ $errors->has('items.'.$index.'.qty') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}
                                              disabled:bg-slate-50 disabled:text-slate-400">
                                @error('items.'.$index.'.qty')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-4 py-3">
                                <button type="button" wire:click="removeItem({{ $index }})"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500">
                                    <x-heroicon-m-x-mark class="h-4 w-4"/>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="rounded-xl border border-dashed border-slate-200 px-5 py-8 text-center">
                <x-heroicon-o-cube class="mx-auto mb-2 h-8 w-8 text-slate-300"/>
                <p class="text-sm text-slate-400">Belum ada produk. Cari dan pilih produk di atas.</p>
            </div>
            @endif
            @else
            <div class="rounded-xl border border-dashed border-slate-200 px-5 py-8 text-center">
                <p class="text-sm text-slate-400">Pilih salesman terlebih dahulu.</p>
            </div>
            @endif
        </div>
        <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
            <button wire:click="save" wire:loading.attr="disabled"
                    {{ count($items) === 0 ? 'disabled' : '' }}
                    class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-slate-800 disabled:opacity-50">
                <span wire:loading wire:target="save">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                </span>
                Post Loading
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
                    @forelse($loadings as $loading)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $loading->document_number }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $loading->salesman->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $loading->operational_date }}</td>
                        <td class="px-4 py-3">
                            @foreach($loading->items as $item)
                            <p class="text-xs text-slate-600">
                                {{ $item->product->product_name ?? '-' }}
                                @if($item->product?->variant)
                                <span class="text-slate-400">— {{ $item->product->variant }}</span>
                                @endif
                                : {{ number_format($item->qty, 0) }}
                            </p>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $color = match($loading->status) {
                                    'POSTED'    => 'bg-emerald-100 text-emerald-700',
                                    'CANCELLED' => 'bg-red-100 text-red-700',
                                    default     => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $color }}">{{ $loading->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada stock loading.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loadings->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">{{ $loadings->links() }}</div>
        @endif
    </div>
</div>
