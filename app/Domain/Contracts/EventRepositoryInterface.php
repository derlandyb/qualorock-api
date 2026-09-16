<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\Event;
use Illuminate\Support\Collection;

interface EventRepositoryInterface
{
    public function findById(int $id): ?Event;

    /**
     * @return Collection<int, Event>
     */
    public function findByOrganizerId(int $organizerId): Collection;

    public function create(Event $event): Event;

    public function update(int $id, array $attributes): Event;

    public function delete(int $id): void;
}
