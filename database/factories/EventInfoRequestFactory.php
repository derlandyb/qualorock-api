<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\EventInfoRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventInfoRequest>
 */
class EventInfoRequestFactory extends Factory
{
    protected $model = EventInfoRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'consumer_user_id' => fake()->numberBetween(1, 100000),
            'message' => fake()->sentence(),
            'organizer_response' => null,
            'responded_at' => null,
        ];
    }
}
