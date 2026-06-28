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

        $areaSbyBar = Area::where('area_code', 'SBY-BAR')->first();
        $areaSbySel = Area::where('area_code', 'SBY-SEL')->first();
        $areaSbyTim = Area::where('area_code', 'SBY-TIM')->first();

        $customers = [
            [
                'customer_code' => 'CST-001',
                'customer_name' => 'Warung Bu Sari',
                'address' => 'Jl. Tandes Lor No. 12, Surabaya Barat',
                'area_id' => $areaSbyBar?->id ?? 1,
                'customer_type' => 'CASH',
                'status' => 'ACTIVE',
                'latitude' => -7.2697,
                'longitude' => 112.6650,
                'radius_tolerance_meter' => 100,
            ],
            [
                'customer_code' => 'CST-002',
                'customer_name' => 'Toko Berkah Jaya',
                'address' => 'Jl. Manukan Kulon No. 45, Surabaya Barat',
                'area_id' => $areaSbyBar?->id ?? 1,
                'customer_type' => 'CREDIT',
                'status' => 'ACTIVE',
                'latitude' => -7.2452,
                'longitude' => 112.6891,
                'radius_tolerance_meter' => 150,
                'credit_limit' => 2000000,
                'credit_term_days' => 14,
                'owner_name' => 'Pak Hendra',
                'owner_phone' => '081234567890',
            ],
            [
                'customer_code' => 'CST-003',
                'customer_name' => 'Minimarket Sumber Rejeki',
                'address' => 'Jl. Wonokromo No. 88, Surabaya Selatan',
                'area_id' => $areaSbySel?->id ?? 2,
                'customer_type' => 'CREDIT',
                'status' => 'ACTIVE',
                'latitude' => -7.3197,
                'longitude' => 112.7280,
                'radius_tolerance_meter' => 100,
                'credit_limit' => 5000000,
                'credit_term_days' => 30,
                'owner_name' => 'Bu Retno',
                'owner_phone' => '082345678901',
            ],
            [
                'customer_code' => 'CST-004',
                'customer_name' => 'Warung Pak Darto',
                'address' => 'Jl. Jambangan No. 3, Surabaya Selatan',
                'area_id' => $areaSbySel?->id ?? 2,
                'customer_type' => 'CASH',
                'status' => 'ACTIVE',
                'latitude' => -7.3312,
                'longitude' => 112.7185,
                'radius_tolerance_meter' => 100,
            ],
            [
                'customer_code' => 'CST-005',
                'customer_name' => 'Toko Makmur Sentosa',
                'address' => 'Jl. Rungkut Asri No. 21, Surabaya Timur',
                'area_id' => $areaSbyTim?->id ?? 3,
                'customer_type' => 'CREDIT',
                'status' => 'ACTIVE',
                'latitude' => -7.3219,
                'longitude' => 112.7831,
                'radius_tolerance_meter' => 100,
                'credit_limit' => 3000000,
                'credit_term_days' => 14,
                'owner_name' => 'Pak Sugeng',
                'owner_phone' => '083456789012',
            ],
            [
                'customer_code' => 'CST-006',
                'customer_name' => 'Warung Mba Yuni',
                'address' => 'Jl. Gunung Anyar No. 7, Surabaya Timur',
                'area_id' => $areaSbyTim?->id ?? 3,
                'customer_type' => 'CASH',
                'status' => 'ACTIVE',
                'latitude' => -7.3387,
                'longitude' => 112.7950,
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
