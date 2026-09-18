<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\EventEngagement;

interface EventStatsRepositoryInterface
{
    public function findByEventId(int $eventId): ?EventEngagement;

    /**
     * @return array<int, EventEngagement> keyed by event_id
     */
    public function findAllByOrganizerId(int $organizerId): array;
}
