<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['area_code' => 'SBY-BAR', 'area_name' => 'Surabaya Barat'],
            ['area_code' => 'SBY-SEL', 'area_name' => 'Surabaya Selatan'],
            ['area_code' => 'SBY-TIM', 'area_name' => 'Surabaya Timur'],
            ['area_code' => 'SBY-UTA', 'area_name' => 'Surabaya Utara'],
            ['area_code' => 'SBY-PUS', 'area_name' => 'Surabaya Pusat'],
            ['area_code' => 'SDA',     'area_name' => 'Sidoarjo'],
            ['area_code' => 'GSK',     'area_name' => 'Gresik'],
            ['area_code' => 'MJK',     'area_name' => 'Mojokerto'],
        ];

        foreach ($areas as $area) {
            Area::firstOrCreate(
                ['area_code' => $area['area_code']],
                array_merge($area, [
                    'is_active' => true,
                    'created_by' => 1,
                    'created_at' => now(),
                ])
            );
        }
    }
}
