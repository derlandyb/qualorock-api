<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contracts\EventInfoRequestRepositoryInterface;
use App\Domain\Entities\EventInfoRequest as EventInfoRequestEntity;

class EloquentEventInfoRequestRepository implements EventInfoRequestRepositoryInterface
{
    public function findById(int $id): ?EventInfoRequestEntity
    {
        $model = EventInfoRequest::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEventId(int $eventId): array
    {
        return EventInfoRequest::where('event_id', $eventId)
            ->orderBy('created_at')
            ->get()
            ->map(fn (EventInfoRequest $model) => $this->toEntity($model))
            ->all();
    }

    public function respond(int $id, string $response): EventInfoRequestEntity
    {
        $model = EventInfoRequest::findOrFail($id);
        $model->respond($response);

        return $this->toEntity($model);
    }

    private function toEntity(EventInfoRequest $model): EventInfoRequestEntity
    {
        return new EventInfoRequestEntity(
            id: $model->id,
            eventId: $model->event_id,
            consumerUserId: $model->consumer_user_id,
            message: $model->message,
            organizerResponse: $model->organizer_response,
            respondedAt: $model->responded_at?->toImmutable(),
        );
    }
}
