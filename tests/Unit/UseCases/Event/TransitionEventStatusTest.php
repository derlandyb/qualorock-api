<?php

namespace Tests\Unit\UseCases\Event;

use App\Application\UseCases\Event\TransitionEventStatus;
use App\Domain\Entities\Event;
use App\Domain\Enums\EventPriceType;
use App\Domain\Enums\EventStatus;
use App\Domain\Exceptions\EventMissingRequiredFieldsForPublishException;
use App\Domain\Exceptions\InvalidEventStatusTransitionException;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Doubles\FakeEventRepository;
use Tests\TestCase;

class TransitionEventStatusTest extends TestCase
{
    #[Test]
    #[TestDox('GIVEN a required field is missing WHEN attempting to transition to published THEN the request is rejected with the missing fields identified')]
    public function it_rejects_publishing_an_event_with_missing_required_fields(): void
    {
        $repository = new FakeEventRepository;
        $event = $repository->create($this->makeEvent(featuredImageUrl: ''));

        $useCase = new TransitionEventStatus($repository);

        try {
            $useCase->handle($event, EventStatus::Published);
            $this->fail('Expected EventMissingRequiredFieldsForPublishException to be thrown.');
        } catch (EventMissingRequiredFieldsForPublishException $exception) {
            $this->assertSame(['featuredImageUrl'], $exception->missingFields);
        }
    }

    #[Test]
    #[TestDox('GIVEN an invalid transition (cancelled to published) WHEN requested THEN it is rejected')]
    public function it_rejects_an_invalid_status_transition(): void
    {
        $repository = new FakeEventRepository;
        $event = $repository->create($this->makeEvent(status: EventStatus::Cancelled));

        $useCase = new TransitionEventStatus($repository);

        $this->expectException(InvalidEventStatusTransitionException::class);

        $useCase->handle($event, EventStatus::Published);
    }

    #[Test]
    #[TestDox('GIVEN a draft with all required fields WHEN transitioning to published THEN it succeeds and records publishedAt')]
    public function it_publishes_a_complete_draft_event(): void
    {
        $repository = new FakeEventRepository;
        $event = $repository->create($this->makeEvent());

        $useCase = new TransitionEventStatus($repository);
        $updated = $useCase->handle($event, EventStatus::Published);

        $this->assertSame(EventStatus::Published, $updated->status);
        $this->assertNotNull($updated->publishedAt);
    }

    private function makeEvent(
        EventStatus $status = EventStatus::Draft,
        string $featuredImageUrl = 'https://example.com/image.jpg',
    ): Event {
        return new Event(
            id: null,
            organizerId: 1,
            venueId: 1,
            title: 'Test Event',
            description: 'Description',
            dateTime: new DateTimeImmutable('+1 week'),
            location: 'Location',
            fullAddress: 'Full address',
            featuredImageUrl: $featuredImageUrl,
            externalTicketLink: 'https://example.com/tickets',
            priceType: EventPriceType::Free,
            musicCategory: 'Rock',
            capacity: null,
            ageRange: null,
            additionalInfo: null,
            accessibilityInfo: null,
            eventRules: null,
            status: $status,
            publishedAt: null,
        );
    }
}
