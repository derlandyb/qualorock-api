<?php

namespace App\Application\UseCases\EventInfoRequest;

use App\Domain\Contracts\EventInfoRequestRepositoryInterface;
use App\Domain\Entities\EventInfoRequest;

class ListEventInfoRequests
{
    public function __construct(
        private readonly EventInfoRequestRepositoryInterface $eventInfoRequests,
    ) {}

    /**
     * @return array<int, EventInfoRequest>
     */
    public function handle(int $eventId): array
    {
        return $this->eventInfoRequests->findByEventId($eventId);
    }
}
