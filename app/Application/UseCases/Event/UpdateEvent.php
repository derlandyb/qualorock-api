<?php

namespace App\Application\UseCases\Event;

use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\Event;
use App\Domain\Enums\EventPriceType;

class UpdateEvent
{
    /**
     * Maps validated camelCase request fields to their snake_case columns -
     * only fields present here can ever reach the repository's update(),
     * so a caller can never forward arbitrary/unvalidated attributes.
     */
    private const array FIELD_MAP = [
        'venueId' => 'venue_id',
        'title' => 'title',
        'description' => 'description',
        'dateTime' => 'date_time',
        'location' => 'location',
        'fullAddress' => 'full_address',
        'featuredImageUrl' => 'featured_image_url',
        'externalTicketLink' => 'external_ticket_link',
        'priceType' => 'price_type',
        'musicCategory' => 'music_category',
        'capacity' => 'capacity',
        'ageRange' => 'age_range',
        'additionalInfo' => 'additional_info',
        'accessibilityInfo' => 'accessibility_info',
        'eventRules' => 'event_rules',
    ];

    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(int $eventId, array $data): Event
    {
        $attributes = [];

        foreach (self::FIELD_MAP as $camelCase => $column) {
            if (array_key_exists($camelCase, $data)) {
                $attributes[$column] = $data[$camelCase];
            }
        }

        if (array_key_exists('price_type', $attributes)) {
            $attributes['price_type'] = EventPriceType::from($attributes['price_type']);
        }

        return $this->events->update($eventId, $attributes);
    }
}
