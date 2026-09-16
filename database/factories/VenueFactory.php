<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    protected $model = Venue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organizer_id' => Organizer::factory(),
            'name' => fake()->company(),
            'description' => fake()->sentence(),
            'address' => fake()->address(),
            'contact_info' => fake()->phoneNumber(),
            'image_url' => null,
        ];
    }
}
