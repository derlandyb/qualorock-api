<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Entities\Promoter as PromoterEntity;

class EloquentPromoterRepository implements PromoterRepositoryInterface
{
    public function findById(int $id): ?PromoterEntity
    {
        $model = Promoter::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByOrganizerId(int $organizerId): array
    {
        return Promoter::where('organizer_id', $organizerId)
            ->get()
            ->map(fn (Promoter $model) => $this->toEntity($model))
            ->all();
    }

    public function create(PromoterEntity $promoter): PromoterEntity
    {
        $model = Promoter::create([
            'organizer_id' => $promoter->organizerId,
            'name' => $promoter->name,
            'phone' => $promoter->phone,
            'email' => $promoter->email,
            'instagram_url' => $promoter->instagramUrl,
            'tiktok_url' => $promoter->tiktokUrl,
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, array $attributes): PromoterEntity
    {
        $model = Promoter::findOrFail($id);
        $model->update($attributes);

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        $model = Promoter::findOrFail($id);
        $model->events()->detach();
        $model->delete();
    }

    public function linkToEvent(int $promoterId, int $eventId): void
    {
        Promoter::findOrFail($promoterId)->events()->syncWithoutDetaching([$eventId]);
    }

    public function unlinkFromEvent(int $promoterId, int $eventId): void
    {
        Promoter::findOrFail($promoterId)->events()->detach($eventId);
    }

    public function findByEventId(int $eventId): array
    {
        return Event::findOrFail($eventId)->promoters()
            ->get()
            ->map(fn (Promoter $model) => $this->toEntity($model))
            ->all();
    }

    private function toEntity(Promoter $model): PromoterEntity
    {
        return new PromoterEntity(
            id: $model->id,
            organizerId: $model->organizer_id,
            name: $model->name,
            phone: $model->phone,
            email: $model->email,
            instagramUrl: $model->instagram_url,
            tiktokUrl: $model->tiktok_url,
        );
    }
}
