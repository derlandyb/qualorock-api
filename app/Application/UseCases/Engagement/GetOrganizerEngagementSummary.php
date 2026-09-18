<?php

namespace App\Application\UseCases\Engagement;

use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Contracts\EventStatsRepositoryInterface;
use App\Domain\Entities\EventEngagement;

class GetOrganizerEngagementSummary
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
        private readonly EventStatsRepositoryInterface $eventStats,
    ) {}

    /**
     * @return array<int, EventEngagement>
     */
    public function handle(int $organizerId): array
    {
        $events = $this->events->findByOrganizerId($organizerId);
        $stats = $this->eventStats->findAllByOrganizerId($organizerId);

        return array_map(
            fn ($event) => $stats[$event->id] ?? new EventEngagement(
                eventId: $event->id,
                viewsCount: 0,
                favoritesCount: 0,
                ticketLinkClicksCount: 0,
                interestCount: 0,
            ),
            $events,
        );
    }
}
