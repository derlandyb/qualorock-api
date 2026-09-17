<?php

namespace App\Domain\Entities;

use App\Domain\Enums\DataExportRequestStatus;
use DateTimeImmutable;

final class DataExportRequest
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $organizerId,
        public readonly DataExportRequestStatus $status,
        public readonly ?string $downloadPath,
        public readonly DateTimeImmutable $requestedAt,
    ) {}
}
