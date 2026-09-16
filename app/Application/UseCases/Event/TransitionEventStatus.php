<?php

namespace App\Application\UseCases\Event;

use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\Event;
use App\Domain\Enums\EventStatus;
use App\Domain\Exceptions\EventMissingRequiredFieldsForPublishException;
use App\Domain\Exceptions\InvalidEventStatusTransitionException;

class TransitionEventStatus
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {}

    public function handle(Event $event, EventStatus $target): Event
    {
        if (! $event->canTransitionTo($target)) {
            throw new InvalidEventStatusTransitionException($event->status, $target);
        }

        if ($target === EventStatus::Published) {
            $missingFields = $event->missingFieldsForPublish();

            if ($missingFields !== []) {
                throw new EventMissingRequiredFieldsForPublishException($missingFields);
            }
        }

        return $this->events->update($event->id, [
            'status' => $target,
            'published_at' => $target === EventStatus::Published ? now() : $event->publishedAt,
        ]);
    }
}
