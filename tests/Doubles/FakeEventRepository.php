<?php

namespace Tests\Doubles;

use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\Event;
use DateTimeImmutable;

class FakeEventRepository implements EventRepositoryInterface
{
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
        return null;
    }

    public function findByOrganizerId(int $organizerId): array
    {
        return [];
    }

    public function create(Event $event): Event
    {
        return $event;
    }

    public function update(int $id, array $attributes): Event
    {
        throw new \RuntimeException('Not supported by this fake.');
    }

    public function delete(int $id): void {}

    public function countPublishedBetween(int $organizerId, DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        return count(array_filter(
            $this->publishedEvents,
            fn (array $event): bool => $event['organizerId'] === $organizerId
                && $event['publishedAt'] >= $start
                && $event['publishedAt'] <= $end,
        ));
    }
}
