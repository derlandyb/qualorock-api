<?php

namespace App\Application\UseCases\Event;

use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\Event;
use App\Domain\Enums\EventStatus;

class DuplicateEvent
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {}

    public function handle(Event $original): Event
    {
        return $this->events->create(new Event(
            id: null,
            organizerId: $original->organizerId,
            venueId: $original->venueId,
            title: $original->title,
            description: $original->description,
            dateTime: $original->dateTime,
            location: $original->location,
            fullAddress: $original->fullAddress,
            featuredImageUrl: $original->featuredImageUrl,
            externalTicketLink: $original->externalTicketLink,
            priceType: $original->priceType,
            musicCategory: $original->musicCategory,
            capacity: $original->capacity,
            ageRange: $original->ageRange,
            additionalInfo: $original->additionalInfo,
            accessibilityInfo: $original->accessibilityInfo,
            eventRules: $original->eventRules,
            status: EventStatus::Draft,
            publishedAt: null,
        ));
    }
}
