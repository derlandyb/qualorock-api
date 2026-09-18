<?php

namespace App\Application\UseCases\Engagement;

use App\Domain\Contracts\EventStatsRepositoryInterface;
use App\Domain\Entities\EventEngagement;

class GetEventEngagement
{
    public function __construct(
        private readonly EventStatsRepositoryInterface $eventStats,
    ) {}

    public function handle(int $eventId): EventEngagement
    {
        return $this->eventStats->findByEventId($eventId) ?? new EventEngagement(
            eventId: $eventId,
            viewsCount: 0,
            favoritesCount: 0,
            ticketLinkClicksCount: 0,
            interestCount: 0,
        );
    }
}
