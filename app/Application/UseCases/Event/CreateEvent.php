<?php

namespace App\Application\UseCases\Event;

use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\Event;
use App\Domain\Enums\EventPriceType;
use App\Domain\Enums\EventStatus;
use DateTimeImmutable;

class CreateEvent
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(int $organizerId, array $data): Event
    {
        return $this->events->create(new Event(
            id: null,
            organizerId: $organizerId,
            venueId: (int) $data['venueId'],
            title: $data['title'],
            description: $data['description'],
            dateTime: new DateTimeImmutable($data['dateTime']),
            location: $data['location'],
            fullAddress: $data['fullAddress'],
            featuredImageUrl: $data['featuredImageUrl'],
            externalTicketLink: $data['externalTicketLink'],
            priceType: EventPriceType::from($data['priceType']),
            musicCategory: $data['musicCategory'],
            capacity: $data['capacity'] ?? null,
            ageRange: $data['ageRange'] ?? null,
            additionalInfo: $data['additionalInfo'] ?? null,
            accessibilityInfo: $data['accessibilityInfo'] ?? null,
            eventRules: $data['eventRules'] ?? null,
            status: EventStatus::Draft,
            publishedAt: null,
        ));
    }
}
