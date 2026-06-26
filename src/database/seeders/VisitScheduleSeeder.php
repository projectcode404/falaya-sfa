<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use App\Models\VisitSchedule;
use Illuminate\Database\Seeder;

class VisitScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $salesman = User::where('email', 'salesman@falaya.test')->first();
        if (! $salesman) {
            return;
        }

        // Senin=1, Selasa=2, Rabu=3, Kamis=4, Jumat=5, Sabtu=6
        $schedules = [
            // Jakarta Barat — Senin, Rabu, Jumat
            ['customer_code' => 'CST-001', 'days' => [1, 3, 5]],
            ['customer_code' => 'CST-002', 'days' => [1, 3, 5]],
            // Jakarta Selatan — Selasa, Kamis
            ['customer_code' => 'CST-003', 'days' => [2, 4]],
            ['customer_code' => 'CST-004', 'days' => [2, 4]],
            // Jakarta Timur — Rabu, Sabtu
            ['customer_code' => 'CST-005', 'days' => [3, 6]],
            ['customer_code' => 'CST-006', 'days' => [3, 6]],
        ];

        foreach ($schedules as $item) {
            $customer = Customer::where('customer_code', $item['customer_code'])->first();
            if (! $customer) {
                continue;
            }

            foreach ($item['days'] as $day) {
                VisitSchedule::firstOrCreate(
                    [
                        'salesman_id' => $salesman->id,
                        'customer_id' => $customer->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'is_active' => true,
                        'effective_from' => now()->toDateString(),
                        'effective_to' => null,
                        'created_by' => 1,
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
