<?php

namespace Tests\Unit\Policies;

use App\Application\Policies\EventPolicy;
use App\Domain\Entities\Event;
use App\Domain\Enums\EventPriceType;
use App\Domain\Enums\EventStatus;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class EventPolicyTest extends TestCase
{
    #[Test]
    #[TestDox('GIVEN organizer A WHEN attempting to act on organizer B\'s event THEN the policy denies it')]
    public function it_denies_an_organizer_acting_on_another_organizers_event(): void
    {
        $organizerA = $this->makeOrganizer(id: 1);
        $event = $this->makeEvent(organizerId: 2);

        $this->assertFalse((new EventPolicy)->owns($organizerA, $event));
    }

    #[Test]
    #[TestDox('GIVEN an organizer WHEN acting on their own event THEN the policy allows it')]
    public function it_allows_an_organizer_acting_on_their_own_event(): void
    {
        $organizer = $this->makeOrganizer(id: 1);
        $event = $this->makeEvent(organizerId: 1);

        $this->assertTrue((new EventPolicy)->owns($organizer, $event));
    }

    #[Test]
    #[TestDox('GIVEN no authenticated user WHEN checking event ownership THEN the policy denies it')]
    public function it_denies_when_there_is_no_authenticated_organizer(): void
    {
        $event = $this->makeEvent(organizerId: 1);

        $this->assertFalse((new EventPolicy)->owns(null, $event));
    }

    private function makeOrganizer(int $id): Organizer
    {
        $organizer = new Organizer;
        $organizer->id = $id;

        return $organizer;
    }

    private function makeEvent(int $organizerId): Event
    {
        return new Event(
            id: 10,
            organizerId: $organizerId,
            venueId: 1,
            title: 'Test Event',
            description: 'Description',
            dateTime: new DateTimeImmutable,
            location: 'Location',
            fullAddress: 'Full address',
            featuredImageUrl: 'https://example.com/image.jpg',
            externalTicketLink: 'https://example.com/tickets',
            priceType: EventPriceType::Free,
            musicCategory: 'Rock',
            capacity: null,
            ageRange: null,
            additionalInfo: null,
            accessibilityInfo: null,
            eventRules: null,
            status: EventStatus::Draft,
            publishedAt: null,
        );
    }
}
