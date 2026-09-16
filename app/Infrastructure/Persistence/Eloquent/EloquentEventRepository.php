<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\Event as EventEntity;
use App\Domain\Enums\EventStatus;
use DateTimeImmutable;

class EloquentEventRepository implements EventRepositoryInterface
{
    public function findById(int $id): ?EventEntity
    {
        $model = Event::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByOrganizerId(int $organizerId): array
    {
        return Event::where('organizer_id', $organizerId)
            ->get()
            ->map(fn (Event $model) => $this->toEntity($model))
            ->all();
    }

    public function create(EventEntity $event): EventEntity
    {
        $model = Event::create([
            'organizer_id' => $event->organizerId,
            'venue_id' => $event->venueId,
            'title' => $event->title,
            'description' => $event->description,
            'date_time' => $event->dateTime,
            'location' => $event->location,
            'full_address' => $event->fullAddress,
            'featured_image_url' => $event->featuredImageUrl,
            'external_ticket_link' => $event->externalTicketLink,
            'price_type' => $event->priceType,
            'music_category' => $event->musicCategory,
            'capacity' => $event->capacity,
            'age_range' => $event->ageRange,
            'additional_info' => $event->additionalInfo,
            'accessibility_info' => $event->accessibilityInfo,
            'event_rules' => $event->eventRules,
            'status' => $event->status,
            'published_at' => $event->publishedAt,
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, array $attributes): EventEntity
    {
        $model = Event::findOrFail($id);
        $model->update($attributes);

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        Event::findOrFail($id)->delete();
    }

    public function countPublishedBetween(int $organizerId, DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        return Event::where('organizer_id', $organizerId)
            ->where('status', EventStatus::Published)
            ->whereBetween('published_at', [$start, $end])
            ->count();
    }

    private function toEntity(Event $model): EventEntity
    {
        return new EventEntity(
            id: $model->id,
            organizerId: $model->organizer_id,
            venueId: $model->venue_id,
            title: $model->title,
            description: $model->description,
            dateTime: $model->date_time->toImmutable(),
            location: $model->location,
            fullAddress: $model->full_address,
            featuredImageUrl: $model->featured_image_url,
            externalTicketLink: $model->external_ticket_link,
            priceType: $model->price_type,
            musicCategory: $model->music_category,
            capacity: $model->capacity,
            ageRange: $model->age_range,
            additionalInfo: $model->additional_info,
            accessibilityInfo: $model->accessibility_info,
            eventRules: $model->event_rules,
            status: $model->status,
            publishedAt: $model->published_at?->toImmutable(),
        );
    }
}
