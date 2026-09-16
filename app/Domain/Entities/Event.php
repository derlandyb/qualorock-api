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

    public function canTransitionTo(EventStatus $target): bool
    {
        return match ($this->status) {
            EventStatus::Draft => in_array($target, [EventStatus::Published, EventStatus::Cancelled], true),
            EventStatus::Published => in_array($target, [EventStatus::Cancelled, EventStatus::Closed], true),
            default => false,
        };
    }

    /**
     * @return array<int, string>
     */
    public function missingFieldsForPublish(): array
    {
        $requiredStringFields = [
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'fullAddress' => $this->fullAddress,
            'featuredImageUrl' => $this->featuredImageUrl,
            'externalTicketLink' => $this->externalTicketLink,
            'musicCategory' => $this->musicCategory,
        ];

        return array_keys(array_filter(
            $requiredStringFields,
            fn (string $value): bool => trim($value) === '',
        ));
    }
}
