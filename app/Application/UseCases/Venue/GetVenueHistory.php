<?php

namespace App\Application\UseCases\Venue;

use App\Domain\Contracts\VenueRepositoryInterface;
use App\Domain\Entities\Event;

class GetVenueHistory
{
    public function __construct(
        private readonly VenueRepositoryInterface $venues,
    ) {}

    /**
     * @return array<int, Event>
     */
    public function handle(int $venueId): array
    {
        return $this->venues->findPastEvents($venueId);
    }
}
