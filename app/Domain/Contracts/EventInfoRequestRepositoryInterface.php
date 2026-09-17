<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\EventInfoRequest;

interface EventInfoRequestRepositoryInterface
{
    public function findById(int $id): ?EventInfoRequest;

    /**
     * @return array<int, EventInfoRequest>
     */
    public function findByEventId(int $eventId): array;

    public function respond(int $id, string $response): EventInfoRequest;
}
