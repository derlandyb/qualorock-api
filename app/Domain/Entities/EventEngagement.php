<?php

namespace App\Domain\Entities;

final class EventEngagement
{
    public function __construct(
        public readonly int $eventId,
        public readonly int $viewsCount,
        public readonly int $favoritesCount,
        public readonly int $ticketLinkClicksCount,
        public readonly int $interestCount,
    ) {}
}
