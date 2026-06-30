<div>
    {{-- Header --}}
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Jadwal Kunjungan</h2>
            <p class="text-xs text-slate-400">Template kunjungan rutin salesman per hari</p>
        </div>
        <button wire:click="openCreate"
                class="flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-slate-800">
            <x-heroicon-m-plus class="h-4 w-4"/>
            Tambah Jadwal
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
            <h3 class="text-sm font-semibold text-slate-900">{{ $isEdit ? 'Edit Jadwal' : 'Tambah Jadwal Kunjungan' }}</h3>
        </div>
        <div class="grid grid-cols-1 gap-4 px-5 py-4 md:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Salesman <span class="text-red-500">*</span></label>
                <select wire:model="salesman_id"
                        class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                               {{ $errors->has('salesman_id') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                    <option value="">-- Pilih Salesman --</option>
                    @foreach($salesmen as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('salesman_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Outlet <span class="text-red-500">*</span></label>
                <select wire:model="customer_id"
                        class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                               {{ $errors->has('customer_id') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                    <option value="">-- Pilih Outlet --</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                    @endforeach
                </select>
                @error('customer_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Hari Kunjungan <span class="text-red-500">*</span></label>
                <select wire:model="day_of_week"
                        class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                               {{ $errors->has('day_of_week') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                    <option value="">-- Pilih Hari --</option>
                    @foreach($days as $num => $label)
                    <option value="{{ $num }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('day_of_week') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Berlaku Dari <span class="text-red-500">*</span></label>
                <input type="date" wire:model="effective_from"
                       class="w-full rounded-lg border px-3 py-2.5 text-sm focus:outline-none focus:ring-2
                              {{ $errors->has('effective_from') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                @error('effective_from') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Berlaku Sampai</label>
                <input type="date" wire:model="effective_to"
                       class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                <p class="mt-1 text-xs text-slate-400">Kosongkan = aktif tanpa batas</p>
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
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
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
        <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-5 py-3">
            <div class="relative flex-1 min-w-48">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"/>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama outlet..."
                       class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
            </div>
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
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Salesman</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Outlet</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Hari</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Berlaku</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3 font-semibold text-slate-900">{{ $schedule->salesman->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $schedule->customer->customer_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $days[$schedule->day_of_week] ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            {{ $schedule->effective_from->format('d/m/Y') }}
                            @if($schedule->effective_to)
                                – {{ $schedule->effective_to->format('d/m/Y') }}
                            @else
                                – selamanya
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($schedule->is_active)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $schedule->id }})"
                                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                                    Edit
                                </button>
                                <button wire:click="toggleActive({{ $schedule->id }})"
                                        wire:confirm="{{ $schedule->is_active ? 'Nonaktifkan jadwal ini?' : 'Aktifkan jadwal ini?' }}"
                                        class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors
                                               {{ $schedule->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                                    {{ $schedule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">
                            Belum ada jadwal kunjungan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schedules->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $schedules->links() }}
        </div>
        @endif
    </div>
</div>
