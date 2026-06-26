<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingsSeeder::class,
            UserSeeder::class,
            AreaSeeder::class,
            ProductSeeder::class,
            InitialStockSeeder::class,
            CustomerSeeder::class,
            VisitScheduleSeeder::class,
        ]);
    }
}
