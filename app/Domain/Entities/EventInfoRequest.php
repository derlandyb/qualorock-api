<?php

namespace App\Domain\Entities;

use DateTimeImmutable;

final class EventInfoRequest
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $eventId,
        public readonly int $consumerUserId,
        public readonly string $message,
        public readonly ?string $organizerResponse,
        public readonly ?DateTimeImmutable $respondedAt,
    ) {}
}
