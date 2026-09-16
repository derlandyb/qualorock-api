<?php

namespace Tests\Feature\Organizer;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Enums\EventStatus;
use App\Domain\Enums\OrganizerApprovalState;
use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class VenueControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Referer', 'http://localhost');
    }

    #[Test]
    #[TestDox('GIVEN an organizer WHEN editing their venue name, description, address, contact, or image THEN the change saves and is reflected on show')]
    public function it_updates_the_organizers_own_venue(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $venue = Venue::factory()->for($organizer)->create(['name' => 'Old Name']);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->putJson('/api/admin/v1/organizer/venue', ['name' => 'New Name']);

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'New Name']);
        $this->assertSame('New Name', $venue->fresh()->name);

        $showResponse = $this->getJson('/api/admin/v1/organizer/venue');
        $showResponse->assertOk();
        $showResponse->assertJsonFragment(['name' => 'New Name']);
    }

    #[Test]
    #[TestDox('GIVEN organizer A WHEN attempting to update organizer B\'s venue THEN organizer A can only ever address their own venue')]
    public function it_denies_updating_another_organizers_venue(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $venueB = Venue::factory()->for($organizerB)->create(['name' => 'Venue B']);

        $response = $this->actingAsApprovedOrganizer($organizerA)
            ->putJson('/api/admin/v1/organizer/venue', ['name' => 'Hijacked']);

        $response->assertForbidden();
        $this->assertSame('Venue B', $venueB->fresh()->name);
    }

    #[Test]
    #[TestDox('GIVEN a venue with upcoming published events and a draft event WHEN opening the agenda view THEN only upcoming published events are returned')]
    public function it_shows_only_upcoming_published_events_in_the_agenda(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $venue = Venue::factory()->for($organizer)->create();

        $upcomingPublished = Event::factory()->for($organizer, 'organizer')->for($venue)
            ->published()->create(['title' => 'Upcoming Published', 'date_time' => now()->addWeek()]);
        Event::factory()->for($organizer, 'organizer')->for($venue)
            ->draft()->create(['title' => 'Draft', 'date_time' => now()->addWeek()]);
        Event::factory()->for($organizer, 'organizer')->for($venue)
            ->published()->create(['title' => 'Past Published', 'date_time' => now()->subWeek()]);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->getJson('/api/admin/v1/organizer/venue/agenda');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Upcoming Published']);
        $this->assertSame($upcomingPublished->id, $response->json('data.0.id'));
    }

    #[Test]
    #[TestDox('GIVEN a venue with past/closed events and future published events WHEN opening the history view THEN only past/closed events are returned')]
    public function it_shows_only_past_or_closed_events_in_the_history(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $venue = Venue::factory()->for($organizer)->create();

        Event::factory()->for($organizer, 'organizer')->for($venue)
            ->published()->create(['title' => 'Past Published', 'date_time' => now()->subWeek()]);
        Event::factory()->for($organizer, 'organizer')->for($venue)
            ->create(['title' => 'Closed Upcoming', 'status' => EventStatus::Closed, 'date_time' => now()->addWeek()]);
        Event::factory()->for($organizer, 'organizer')->for($venue)
            ->published()->create(['title' => 'Future Published', 'date_time' => now()->addWeek()]);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->getJson('/api/admin/v1/organizer/venue/history');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['title' => 'Past Published']);
        $response->assertJsonFragment(['title' => 'Closed Upcoming']);
        $response->assertJsonMissing(['title' => 'Future Published']);
    }

    private function actingAsApprovedOrganizer(Organizer $organizer): self
    {
        $this->withSession([
            AdminPanelConstants::ORGANIZER_APPROVAL_STATE_SESSION_KEY => OrganizerApprovalState::Approved->value,
        ])->actingAs($organizer, 'organizer');

        return $this;
    }
}
