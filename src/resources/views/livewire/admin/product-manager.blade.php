<div>
    {{-- Header --}}
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Manajemen Produk</h2>
            <p class="text-xs text-slate-400">Kelola produk yang dijual oleh salesman</p>
        </div>
        <button wire:click="openCreate"
                class="flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-slate-800">
            <x-heroicon-m-plus class="h-4 w-4"/>
            Tambah Produk
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <x-heroicon-o-check-circle class="h-4 w-4 flex-shrink-0 text-emerald-500"/>
        {{ session('success') }}
    </div>
    @endif

    {{-- Form --}}
    @if($showForm)
    <div class="mb-5 rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-sm font-semibold text-slate-900">{{ $isEdit ? 'Edit Produk' : 'Tambah Produk Baru' }}</h3>
        </div>
        <div class="grid grid-cols-1 gap-4 px-5 py-4 md:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kode Produk <span class="text-red-500">*</span></label>
                <input type="text" wire:model="product_code" placeholder="PRD-001"
                       class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                              {{ $errors->has('product_code') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                @error('product_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" wire:model="product_name" placeholder="Keripik Singkong Original"
                       class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                              {{ $errors->has('product_name') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                @error('product_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Varian</label>
                <input type="text" wire:model="variant" placeholder="Original, Balado, ..."
                       class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kategori</label>
                <input type="text" wire:model="category" placeholder="Keripik"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Satuan <span class="text-red-500">*</span></label>
                <input type="text" wire:model="unit" placeholder="pcs, dus, ..."
                       class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                              {{ $errors->has('unit') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                @error('unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Harga Jual <span class="text-red-500">*</span></label>
                <div class="flex items-center overflow-hidden rounded-lg border {{ $errors->has('selling_price') ? 'border-red-300' : 'border-slate-200' }}">
                    <span class="border-r border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold text-slate-500">Rp</span>
                    <input type="number" wire:model="selling_price" min="0" step="100"
                           class="flex-1 px-3 py-2.5 text-sm focus:outline-none">
                </div>
                @error('selling_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-3 pt-5">
                <label class="relative inline-flex cursor-pointer items-center gap-2">
                    <input type="checkbox" wire:model="is_active" class="peer sr-only">
                    <div class="h-5 w-9 rounded-full bg-slate-200 peer-checked:bg-amber-500 transition-colors after:absolute after:left-0.5 after:top-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-4"></div>
                    <span class="text-xs font-semibold text-slate-700">Aktif</span>
                </label>
            </div>
        </div>
        <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
            <button wire:click="save" wire:loading.attr="disabled"
                    class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-slate-800 disabled:opacity-50">
                <span wire:loading wire:target="save">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                </span>
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Produk' }}
            </button>
            <button wire:click="cancelForm"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                Batal
            </button>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 px-5 py-3">
            <div class="relative max-w-xs">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"/>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau kode produk..."
                       class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Produk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Varian</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Satuan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Harga Jual</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/50 transition-colors {{ $product->trashed() ? 'opacity-50' : '' }}">
                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $product->product_code }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $product->product_name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $product->variant ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $product->unit }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($product->trashed())
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Dihapus</span>
                            @elseif($product->is_active)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if(!$product->trashed())
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $product->id }})"
                                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                                    Edit
                                </button>
                                <button wire:click="toggleActive({{ $product->id }})"
                                        wire:confirm="{{ $product->is_active ? 'Nonaktifkan produk ini?' : 'Aktifkan produk ini?' }}"
                                        class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors
                                               {{ $product->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                                    {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">
                            Belum ada produk. Klik "Tambah Produk" untuk memulai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
