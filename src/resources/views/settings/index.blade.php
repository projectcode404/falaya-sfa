<x-layouts.app heading="Settings & Konfigurasi">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('reports.settings') }}">
            @csrf

            {{-- Company Profile --}}
            <div class="mb-5 rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Company Profile</h2>
                    <p class="mt-0.5 text-xs text-slate-400">Informasi perusahaan yang tampil di invoice dan laporan</p>
                </div>
                <div class="px-5 py-4 space-y-4">
                    @php
                        $profileKeys = [
                            'company_name'    => ['label' => 'Nama Perusahaan', 'placeholder' => 'CV Falaya'],
                            'company_address' => ['label' => 'Alamat',          'placeholder' => 'Jl. Merdeka No. 1'],
                            'company_phone'   => ['label' => 'Nomor Telepon',   'placeholder' => '08123456789'],
                        ];
                    @endphp
                    @foreach($profileKeys as $key => $meta)
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">{{ $meta['label'] }}</label>
                        <input type="text"
                               name="settings[{{ $key }}]"
                               value="{{ $settings[$key]->setting_value ?? '' }}"
                               placeholder="{{ $meta['placeholder'] }}"
                               class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition-colors focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Konfigurasi Operasional --}}
            <div class="mb-6 rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Konfigurasi Operasional</h2>
                    <p class="mt-0.5 text-xs text-slate-400">Parameter sistem untuk operasional harian</p>
                </div>
                <div class="px-5 py-4 space-y-4">
                    @php
                        $opsKeys = [
                            'default_radius_tolerance_meter'   => ['label' => 'Radius GPS Default',             'placeholder' => '100',   'suffix' => 'meter'],
                            'gps_low_accuracy_threshold_meter' => ['label' => 'Threshold Akurasi GPS Rendah',   'placeholder' => '100',   'suffix' => 'meter'],
                            'collection_due_soon_days'         => ['label' => 'Hari Mendekati Jatuh Tempo',     'placeholder' => '3',     'suffix' => 'hari'],
                            'cash_reconciliation_threshold'    => ['label' => 'Toleransi Selisih Rekonsiliasi', 'placeholder' => '5000',  'suffix' => 'Rp'],
                        ];
                    @endphp
                    @foreach($opsKeys as $key => $meta)
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">{{ $meta['label'] }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number"
                                   name="settings[{{ $key }}]"
                                   value="{{ $settings[$key]->setting_value ?? '' }}"
                                   placeholder="{{ $meta['placeholder'] }}"
                                   class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition-colors focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                            <span class="flex-shrink-0 text-xs font-semibold text-slate-400">{{ $meta['suffix'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-slate-800">
                Simpan Settings
            </button>

        </form>
    </div>

</x-layouts.app>
