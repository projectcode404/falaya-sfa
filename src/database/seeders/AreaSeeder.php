<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['area_code' => 'JKT-BAR', 'area_name' => 'Jakarta Barat'],
            ['area_code' => 'JKT-SEL', 'area_name' => 'Jakarta Selatan'],
            ['area_code' => 'JKT-TIM', 'area_name' => 'Jakarta Timur'],
            ['area_code' => 'JKT-UTA', 'area_name' => 'Jakarta Utara'],
            ['area_code' => 'JKT-PUS', 'area_name' => 'Jakarta Pusat'],
            ['area_code' => 'TGR',     'area_name' => 'Tangerang'],
            ['area_code' => 'BKS',     'area_name' => 'Bekasi'],
            ['area_code' => 'DPK',     'area_name' => 'Depok'],
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
