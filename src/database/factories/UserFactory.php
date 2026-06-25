<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'SALESMAN',
            'is_active' => true,
            'current_session_token' => null,
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'OWNER']);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'ADMIN']);
    }

    public function salesman(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'SALESMAN']);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
