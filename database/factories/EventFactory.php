<?php

namespace Database\Factories;

use App\Domain\Enums\EventPriceType;
use App\Domain\Enums\EventStatus;
use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organizer_id' => Organizer::factory(),
            'venue_id' => Venue::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'date_time' => fake()->dateTimeBetween('now', '+3 months'),
            'location' => fake()->city(),
            'full_address' => fake()->address(),
            'featured_image_url' => fake()->imageUrl(),
            'external_ticket_link' => fake()->url(),
            'price_type' => EventPriceType::Free,
            'music_category' => fake()->word(),
            'capacity' => null,
            'age_range' => null,
            'additional_info' => null,
            'accessibility_info' => null,
            'event_rules' => null,
            'status' => EventStatus::Draft,
            'published_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Cancelled,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Closed,
        ]);
    }
}
