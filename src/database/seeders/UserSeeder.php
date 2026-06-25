<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Owner', 'email' => 'owner@falaya.test', 'role' => 'OWNER'],
            ['name' => 'Admin', 'email' => 'admin@falaya.test', 'role' => 'ADMIN'],
            ['name' => 'Salesman', 'email' => 'salesman@falaya.test', 'role' => 'SALESMAN'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => $data['role'],
                    'is_active' => true,
                ]
            );
            $user->assignRole($data['role']);
        }
    }
}
