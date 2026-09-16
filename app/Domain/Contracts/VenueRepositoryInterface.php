<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\Event;
use App\Domain\Entities\Venue;

interface VenueRepositoryInterface
{
    public function findById(int $id): ?Venue;

    public function findByOrganizerId(int $organizerId): ?Venue;

    public function update(int $id, array $attributes): Venue;

    /**
     * @return array<int, Event>
     */
    public function findUpcomingPublishedEvents(int $venueId): array;

    /**
     * @return array<int, Event>
     */
    public function findPastEvents(int $venueId): array;
}
