<x-layouts.app heading="Tutup Hari Operasional">

    <div class="mx-auto max-w-2xl">

        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900">
                    Closing — {{ $today->translatedFormat('l, d F Y') }}
                </h2>
            </div>

            <div class="px-6 py-5">

                @if($result['can_close'])

                    {{-- Siap closing --}}
                    <div class="mb-5 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                        <x-heroicon-o-check-circle class="h-5 w-5 flex-shrink-0 text-emerald-500 mt-0.5"/>
                        <div>
                            <p class="text-sm font-semibold text-emerald-800">Semua transaksi sudah selesai.</p>
                            <p class="text-xs text-emerald-700 mt-0.5">Siap menutup hari operasional.</p>
                        </div>
                    </div>

                    @if(isset($result['summary']))
                    <ul class="mb-5 space-y-1">
                        @foreach($result['summary'] as $line)
                        <li class="flex items-center gap-2 text-xs text-slate-500">
                            <span class="h-1 w-1 rounded-full bg-slate-400"></span>
                            {{ $line }}
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <form method="POST" action="{{ route('admin.closing.execute') }}"
                          onsubmit="return confirm('Tutup hari operasional {{ $today->toDateString() }}? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        <button type="submit"
                            class="w-full rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-slate-800">
                            Tutup Hari Operasional Sekarang
                        </button>
                    </form>

                @else

                    {{-- Ada blocker --}}
                    <div class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                        <x-heroicon-o-x-circle class="h-5 w-5 flex-shrink-0 text-red-500 mt-0.5"/>
                        <div>
                            <p class="text-sm font-semibold text-red-800">Belum bisa ditutup.</p>
                            <p class="text-xs text-red-700 mt-0.5">{{ count($result['errors']) }} hal perlu diselesaikan terlebih dahulu.</p>
                        </div>
                    </div>

                    <div class="mb-5 space-y-2">
                        @foreach($result['errors'] as $blocker)
                        <div class="flex items-start justify-between gap-4 rounded-lg border border-red-100 bg-red-50/50 px-4 py-3">
                            <div class="flex items-start gap-3">
                                <x-heroicon-o-exclamation-circle class="h-4 w-4 flex-shrink-0 text-red-500 mt-0.5"/>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $blocker['message'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mb-5 flex items-start gap-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3">
                        <x-heroicon-o-information-circle class="h-4 w-4 flex-shrink-0 text-blue-500 mt-0.5"/>
                        <p class="text-xs text-blue-700">
                            Pengajuan approval (Stock Adjustment, Customer Return, dll) tidak menghalangi penutupan hari.
                        </p>
                    </div>

                    <a href="{{ route('admin.closing') }}"
                       class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                        <x-heroicon-o-arrow-path class="h-4 w-4"/>
                        Cek Ulang Status
                    </a>

                @endif

            </div>
        </div>

    </div>

</x-layouts.app>
