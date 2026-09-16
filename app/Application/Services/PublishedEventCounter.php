<?php

namespace App\Application\Services;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Contracts\EventRepositoryInterface;
use Carbon\CarbonImmutable;
use DateTimeImmutable;

class PublishedEventCounter
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {}

    public function countForCurrentMonth(int $organizerId, ?DateTimeImmutable $now = null): int
    {
        $reference = $now !== null
            ? CarbonImmutable::instance($now)->setTimezone(AdminPanelConstants::DEFAULT_ORGANIZER_TIMEZONE)
            : CarbonImmutable::now(AdminPanelConstants::DEFAULT_ORGANIZER_TIMEZONE);

        $start = $reference->startOfMonth()->utc();
        $end = $reference->endOfMonth()->utc();

        return $this->events->countPublishedBetween($organizerId, $start, $end);
    }
}
