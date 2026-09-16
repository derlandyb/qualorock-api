<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\Promoter;

interface PromoterRepositoryInterface
{
    public function findById(int $id): ?Promoter;

    /**
     * @return array<int, Promoter>
     */
    public function findByOrganizerId(int $organizerId): array;

    public function create(Promoter $promoter): Promoter;

    public function update(int $id, array $attributes): Promoter;

    public function delete(int $id): void;

    public function linkToEvent(int $promoterId, int $eventId): void;

    public function unlinkFromEvent(int $promoterId, int $eventId): void;

    /**
     * @return array<int, Promoter>
     */
    public function findByEventId(int $eventId): array;
}
