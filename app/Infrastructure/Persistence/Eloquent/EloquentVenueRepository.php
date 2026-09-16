<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contracts\VenueRepositoryInterface;
use App\Domain\Entities\Venue as VenueEntity;
use App\Domain\Enums\EventStatus;
use App\Infrastructure\Persistence\Eloquent\Mappers\EventEntityMapper;

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
            ->map(fn (Event $model) => EventEntityMapper::toEntity($model))
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
            ->map(fn (Event $model) => EventEntityMapper::toEntity($model))
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
}
