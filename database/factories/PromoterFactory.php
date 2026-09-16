<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\Promoter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promoter>
 */
class PromoterFactory extends Factory
{
    protected $model = Promoter::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organizer_id' => Organizer::factory(),
            'name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'instagram_url' => fake()->url(),
            'tiktok_url' => fake()->url(),
        ];
    }
}
