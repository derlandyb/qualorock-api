<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\SuperAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<SuperAdmin>
 */
class SuperAdminFactory extends Factory
{
    protected $model = SuperAdmin::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => Hash::make('password'),
        ];
    }
}
