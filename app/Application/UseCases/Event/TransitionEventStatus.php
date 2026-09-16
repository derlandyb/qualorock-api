<?php

namespace App\Application\UseCases\Event;

use App\Application\Services\PublishedEventCounter;
use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\Event;
use App\Domain\Enums\EventStatus;
use App\Domain\Enums\PlanTier;
use App\Domain\Exceptions\BasicTierPublishCapExceededException;
use App\Domain\Exceptions\EventMissingRequiredFieldsForPublishException;
use App\Domain\Exceptions\InvalidEventStatusTransitionException;

class TransitionEventStatus
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
        private readonly PublishedEventCounter $publishedEventCounter,
    ) {}

    public function handle(Event $event, EventStatus $target, PlanTier $organizerPlanTier): Event
    {
        if (! $event->canTransitionTo($target)) {
            throw new InvalidEventStatusTransitionException($event->status, $target);
        }

        if ($target === EventStatus::Published) {
            $missingFields = $event->missingFieldsForPublish();

            if ($missingFields !== []) {
                throw new EventMissingRequiredFieldsForPublishException($missingFields);
            }

            if ($organizerPlanTier === PlanTier::Basic && $this->hasReachedBasicTierPublishCap($event->organizerId)) {
                throw new BasicTierPublishCapExceededException;
            }
        }

        return $this->events->update($event->id, [
            'status' => $target,
            'published_at' => $target === EventStatus::Published ? now() : $event->publishedAt,
        ]);
    }

    private function hasReachedBasicTierPublishCap(int $organizerId): bool
    {
        return $this->publishedEventCounter->countForCurrentMonth($organizerId)
            >= AdminPanelConstants::BASIC_TIER_MONTHLY_PUBLISH_LIMIT;
    }
}
