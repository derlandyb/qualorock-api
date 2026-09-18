<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\EventStats;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventStats>
 */
class EventStatsFactory extends Factory
{
    protected $model = EventStats::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'views_count' => fake()->numberBetween(0, 1000),
            'favorites_count' => fake()->numberBetween(0, 500),
            'ticket_link_clicks_count' => fake()->numberBetween(0, 300),
            'interest_count' => fake()->numberBetween(0, 500),
        ];
    }
}
