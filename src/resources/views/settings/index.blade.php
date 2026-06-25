<x-layouts.app heading="Settings & Konfigurasi">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('reports.settings') }}">
                @csrf

                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Company Profile</h3></div>
                    <div class="card-body">
                        @php
                            $profileKeys = [
                                'company_name'    => 'Nama Perusahaan',
                                'company_address' => 'Alamat',
                                'company_phone'   => 'Nomor Telepon',
                            ];
                        @endphp
                        @foreach($profileKeys as $key => $label)
                            <div class="mb-3">
                                <label class="form-label">{{ $label }}</label>
                                <input type="text" name="settings[{{ $key }}]"
                                       class="form-control"
                                       value="{{ $settings[$key]->setting_value ?? '' }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Konfigurasi Operasional</h3></div>
                    <div class="card-body">
                        @php
                            $opsKeys = [
                                'default_radius_tolerance_meter' => 'Radius GPS Default (meter)',
                                'gps_low_accuracy_threshold_meter' => 'Threshold Akurasi GPS Rendah (meter)',
                                'collection_due_soon_days' => 'Hari Mendekati Jatuh Tempo (hari)',
                                'cash_reconciliation_threshold' => 'Toleransi Selisih Cash Reconciliation (Rp)',
                            ];
                        @endphp
                        @foreach($opsKeys as $key => $label)
                            <div class="mb-3">
                                <label class="form-label">{{ $label }}</label>
                                <input type="number" name="settings[{{ $key }}]"
                                       class="form-control"
                                       value="{{ $settings[$key]->setting_value ?? '' }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Simpan Settings</button>
            </form>
        </div>
    </div>
</x-layouts.app>
