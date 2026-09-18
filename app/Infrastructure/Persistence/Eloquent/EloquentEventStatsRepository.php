<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contracts\EventStatsRepositoryInterface;
use App\Domain\Entities\EventEngagement;

class EloquentEventStatsRepository implements EventStatsRepositoryInterface
{
    public function findByEventId(int $eventId): ?EventEngagement
    {
        $model = EventStats::where('event_id', $eventId)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAllByOrganizerId(int $organizerId): array
    {
        return EventStats::whereHas('event', fn ($query) => $query->where('organizer_id', $organizerId))
            ->get()
            ->mapWithKeys(fn (EventStats $model) => [$model->event_id => $this->toEntity($model)])
            ->all();
    }

    private function toEntity(EventStats $model): EventEngagement
    {
        return new EventEngagement(
            eventId: $model->event_id,
            viewsCount: $model->views_count,
            favoritesCount: $model->favorites_count,
            ticketLinkClicksCount: $model->ticket_link_clicks_count,
            interestCount: $model->interest_count,
        );
    }
}
