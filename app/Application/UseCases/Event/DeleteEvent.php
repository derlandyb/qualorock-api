<?php

namespace App\Application\UseCases\Event;

use App\Domain\Contracts\EventRepositoryInterface;

class DeleteEvent
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {}

    public function handle(int $eventId): void
    {
        $this->events->delete($eventId);
    }
}
