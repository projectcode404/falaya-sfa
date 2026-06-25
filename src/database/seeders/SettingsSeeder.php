<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'setting_key' => 'default_radius_tolerance_meter',
                'setting_value' => '100',
                'setting_type' => 'NUMBER',
                'description' => 'Radius toleransi GPS check-in default (meter)',
            ],
            [
                'setting_key' => 'gps_low_accuracy_threshold_meter',
                'setting_value' => '100',
                'setting_type' => 'NUMBER',
                'description' => 'Threshold akurasi GPS rendah (meter)',
            ],
            [
                'setting_key' => 'collection_due_soon_days',
                'setting_value' => '3',
                'setting_type' => 'NUMBER',
                'description' => 'Jumlah hari sebelum jatuh tempo dianggap DUE_SOON',
            ],
            [
                'setting_key' => 'cash_reconciliation_threshold',
                'setting_value' => '5000',
                'setting_type' => 'NUMBER',
                'description' => 'Toleransi selisih cash reconciliation (Rupiah)',
            ],
            [
                'setting_key' => 'company_name',
                'setting_value' => 'Falaya',
                'setting_type' => 'STRING',
                'description' => 'Nama perusahaan untuk invoice/laporan',
            ],
            [
                'setting_key' => 'company_address',
                'setting_value' => '',
                'setting_type' => 'STRING',
                'description' => 'Alamat perusahaan untuk invoice/laporan',
            ],
            [
                'setting_key' => 'company_logo_url',
                'setting_value' => '',
                'setting_type' => 'STRING',
                'description' => 'URL logo perusahaan',
            ],
            [
                'setting_key' => 'company_primary_color',
                'setting_value' => '#0d6efd',
                'setting_type' => 'STRING',
                'description' => 'Warna utama brand untuk PWA manifest',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }
}
