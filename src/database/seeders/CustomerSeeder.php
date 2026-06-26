<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $salesman = User::where('email', 'salesman@falaya.test')->first();
        if (! $salesman) {
            return;
        }

        $areaJktBar = Area::where('area_code', 'JKT-BAR')->first();
        $areaJktSel = Area::where('area_code', 'JKT-SEL')->first();
        $areaJktTim = Area::where('area_code', 'JKT-TIM')->first();

        $customers = [
            [
                'customer_code' => 'CST-001',
                'customer_name' => 'Warung Bu Sari',
                'address' => 'Jl. Kebon Jeruk No. 12, Jakarta Barat',
                'area_id' => $areaJktBar?->id ?? 1,
                'customer_type' => 'CASH',
                'status' => 'ACTIVE',
                'latitude' => -6.1944,
                'longitude' => 106.7749,
                'radius_tolerance_meter' => 100,
            ],
            [
                'customer_code' => 'CST-002',
                'customer_name' => 'Toko Berkah Jaya',
                'address' => 'Jl. Puri Indah No. 45, Jakarta Barat',
                'area_id' => $areaJktBar?->id ?? 1,
                'customer_type' => 'CREDIT',
                'status' => 'ACTIVE',
                'latitude' => -6.1876,
                'longitude' => 106.7463,
                'radius_tolerance_meter' => 150,
                'credit_limit' => 2000000,
                'credit_term_days' => 14,
                'owner_name' => 'Pak Hendra',
                'owner_phone' => '081234567890',
            ],
            [
                'customer_code' => 'CST-003',
                'customer_name' => 'Minimarket Sumber Rejeki',
                'address' => 'Jl. Fatmawati No. 88, Jakarta Selatan',
                'area_id' => $areaJktSel?->id ?? 2,
                'customer_type' => 'CREDIT',
                'status' => 'ACTIVE',
                'latitude' => -6.2943,
                'longitude' => 106.7946,
                'radius_tolerance_meter' => 100,
                'credit_limit' => 5000000,
                'credit_term_days' => 30,
                'owner_name' => 'Bu Retno',
                'owner_phone' => '082345678901',
            ],
            [
                'customer_code' => 'CST-004',
                'customer_name' => 'Warung Pak Darto',
                'address' => 'Jl. Cempaka No. 3, Jakarta Selatan',
                'area_id' => $areaJktSel?->id ?? 2,
                'customer_type' => 'CASH',
                'status' => 'ACTIVE',
                'latitude' => -6.2611,
                'longitude' => 106.8117,
                'radius_tolerance_meter' => 100,
            ],
            [
                'customer_code' => 'CST-005',
                'customer_name' => 'Toko Makmur Sentosa',
                'address' => 'Jl. Kramat Jati No. 21, Jakarta Timur',
                'area_id' => $areaJktTim?->id ?? 3,
                'customer_type' => 'CREDIT',
                'status' => 'ACTIVE',
                'latitude' => -6.2741,
                'longitude' => 106.8652,
                'radius_tolerance_meter' => 100,
                'credit_limit' => 3000000,
                'credit_term_days' => 14,
                'owner_name' => 'Pak Sugeng',
                'owner_phone' => '083456789012',
            ],
            [
                'customer_code' => 'CST-006',
                'customer_name' => 'Warung Mba Yuni',
                'address' => 'Jl. Pondok Kopi No. 7, Jakarta Timur',
                'area_id' => $areaJktTim?->id ?? 3,
                'customer_type' => 'CASH',
                'status' => 'ACTIVE',
                'latitude' => -6.2512,
                'longitude' => 106.9023,
                'radius_tolerance_meter' => 100,
            ],
        ];

        foreach ($customers as $data) {
            Customer::firstOrCreate(
                ['customer_code' => $data['customer_code']],
                array_merge($data, [
                    'requested_by' => $salesman->id,
                    'approved_by' => 1,
                    'approved_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
