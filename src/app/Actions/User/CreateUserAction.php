<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateUserAction
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => $data['role'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Assign Spatie role sesuai kolom role
            $user->assignRole($data['role']);

            return $user;
        });
    }
}
