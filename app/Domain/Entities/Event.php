<?php

namespace App\Domain\Entities;

use App\Domain\Enums\EventPriceType;
use App\Domain\Enums\EventStatus;
use DateTimeImmutable;

final class Event
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $organizerId,
        public readonly int $venueId,
        public readonly string $title,
        public readonly string $description,
        public readonly DateTimeImmutable $dateTime,
        public readonly string $location,
        public readonly string $fullAddress,
        public readonly string $featuredImageUrl,
        public readonly string $externalTicketLink,
        public readonly EventPriceType $priceType,
        public readonly string $musicCategory,
        public readonly ?int $capacity,
        public readonly ?string $ageRange,
        public readonly ?string $additionalInfo,
        public readonly ?string $accessibilityInfo,
        public readonly ?string $eventRules,
        public readonly EventStatus $status,
        public readonly ?DateTimeImmutable $publishedAt,
    ) {}
}
