<?php

namespace Tests\Doubles;

use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\Event;
use App\Domain\Enums\EventPriceType;
use App\Domain\Enums\EventStatus;
use DateTimeImmutable;
use DateTimeInterface;

class FakeEventRepository implements EventRepositoryInterface
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $rows = [];

    private int $nextId = 1;

    /**
     * @var array<int, array{organizerId: int, publishedAt: DateTimeImmutable}>
     */
    private array $publishedEvents = [];

    public function addPublishedEvent(int $organizerId, DateTimeImmutable $publishedAt): void
    {
        $this->publishedEvents[] = ['organizerId' => $organizerId, 'publishedAt' => $publishedAt];
    }

    public function findById(int $id): ?Event
    {
        return isset($this->rows[$id]) ? $this->toEntity($this->rows[$id]) : null;
    }

    public function findByOrganizerId(int $organizerId): array
    {
        return array_values(array_map(
            fn (array $row): Event => $this->toEntity($row),
            array_filter($this->rows, fn (array $row): bool => $row['organizer_id'] === $organizerId),
        ));
    }

    public function create(Event $event): Event
    {
        $id = $this->nextId++;

        $this->rows[$id] = [
            'id' => $id,
            'organizer_id' => $event->organizerId,
            'venue_id' => $event->venueId,
            'title' => $event->title,
            'description' => $event->description,
            'date_time' => $event->dateTime,
            'location' => $event->location,
            'full_address' => $event->fullAddress,
            'featured_image_url' => $event->featuredImageUrl,
            'external_ticket_link' => $event->externalTicketLink,
            'price_type' => $event->priceType,
            'music_category' => $event->musicCategory,
            'capacity' => $event->capacity,
            'age_range' => $event->ageRange,
            'additional_info' => $event->additionalInfo,
            'accessibility_info' => $event->accessibilityInfo,
            'event_rules' => $event->eventRules,
            'status' => $event->status,
            'published_at' => $event->publishedAt,
        ];

        return $this->toEntity($this->rows[$id]);
    }

    public function update(int $id, array $attributes): Event
    {
        $this->rows[$id] = array_merge($this->rows[$id], $attributes);

        return $this->toEntity($this->rows[$id]);
    }

    public function delete(int $id): void
    {
        unset($this->rows[$id]);
    }

    public function countPublishedBetween(int $organizerId, DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        return count(array_filter(
            $this->publishedEvents,
            fn (array $event): bool => $event['organizerId'] === $organizerId
                && $event['publishedAt'] >= $start
                && $event['publishedAt'] <= $end,
        ));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function toEntity(array $row): Event
    {
        return new Event(
            id: $row['id'],
            organizerId: $row['organizer_id'],
            venueId: $row['venue_id'],
            title: $row['title'],
            description: $row['description'],
            dateTime: DateTimeImmutable::createFromInterface($row['date_time']),
            location: $row['location'],
            fullAddress: $row['full_address'],
            featuredImageUrl: $row['featured_image_url'],
            externalTicketLink: $row['external_ticket_link'],
            priceType: $row['price_type'] instanceof EventPriceType ? $row['price_type'] : EventPriceType::from($row['price_type']),
            musicCategory: $row['music_category'],
            capacity: $row['capacity'],
            ageRange: $row['age_range'],
            additionalInfo: $row['additional_info'],
            accessibilityInfo: $row['accessibility_info'],
            eventRules: $row['event_rules'],
            status: $row['status'] instanceof EventStatus ? $row['status'] : EventStatus::from($row['status']),
            publishedAt: $this->toImmutable($row['published_at']),
        );
    }

    private function toImmutable(?DateTimeInterface $value): ?DateTimeImmutable
    {
        return $value === null ? null : DateTimeImmutable::createFromInterface($value);
    }
}
