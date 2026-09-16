<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\Event;
use DateTimeImmutable;

interface EventRepositoryInterface
{
    public function findById(int $id): ?Event;

    /**
     * @return array<int, Event>
     */
    public function findByOrganizerId(int $organizerId): array;

    public function create(Event $event): Event;

    public function update(int $id, array $attributes): Event;

    public function delete(int $id): void;

    public function countPublishedBetween(int $organizerId, DateTimeImmutable $start, DateTimeImmutable $end): int;
}
