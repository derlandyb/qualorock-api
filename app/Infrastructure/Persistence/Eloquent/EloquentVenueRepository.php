<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contracts\VenueRepositoryInterface;
use App\Domain\Entities\Event as EventEntity;
use App\Domain\Entities\Venue as VenueEntity;
use App\Domain\Enums\EventStatus;

class EloquentVenueRepository implements VenueRepositoryInterface
{
    public function findById(int $id): ?VenueEntity
    {
        $model = Venue::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByOrganizerId(int $organizerId): ?VenueEntity
    {
        $model = Venue::where('organizer_id', $organizerId)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function update(int $id, array $attributes): VenueEntity
    {
        $model = Venue::findOrFail($id);
        $model->update($attributes);

        return $this->toEntity($model);
    }

    public function findUpcomingPublishedEvents(int $venueId): array
    {
        return Event::where('venue_id', $venueId)
            ->where('status', EventStatus::Published)
            ->where('date_time', '>=', now())
            ->orderBy('date_time')
            ->get()
            ->map(fn (Event $model) => $this->toEventEntity($model))
            ->all();
    }

    public function findPastEvents(int $venueId): array
    {
        return Event::where('venue_id', $venueId)
            ->where(function ($query): void {
                $query->where('status', EventStatus::Closed)
                    ->orWhere('date_time', '<', now());
            })
            ->orderBy('date_time', 'desc')
            ->get()
            ->map(fn (Event $model) => $this->toEventEntity($model))
            ->all();
    }

    private function toEntity(Venue $model): VenueEntity
    {
        return new VenueEntity(
            id: $model->id,
            organizerId: $model->organizer_id,
            name: $model->name,
            description: $model->description,
            address: $model->address,
            contactInfo: $model->contact_info,
            imageUrl: $model->image_url,
        );
    }

    private function toEventEntity(Event $model): EventEntity
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
