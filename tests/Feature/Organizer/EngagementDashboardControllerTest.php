<?php

namespace Tests\Feature\Organizer;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Enums\OrganizerApprovalState;
use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\EventStats;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class EngagementDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Referer', 'http://localhost');
    }

    #[Test]
    #[TestDox('GIVEN an event with recorded views/favorites/clicks/interest WHEN the organizer requests its dashboard THEN all four counts are returned')]
    public function it_returns_all_four_counts_for_a_single_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->create();
        EventStats::factory()->for($event)->create([
            'views_count' => 42,
            'favorites_count' => 10,
            'ticket_link_clicks_count' => 5,
            'interest_count' => 20,
        ]);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->getJson("/api/admin/v1/organizer/events/{$event->id}/engagement");

        $response->assertOk();
        $response->assertJsonFragment([
            'eventId' => $event->id,
            'viewsCount' => 42,
            'favoritesCount' => 10,
            'ticketLinkClicksCount' => 5,
            'interestCount' => 20,
        ]);
    }

    #[Test]
    #[TestDox('GIVEN multiple events WHEN requesting the aggregate view THEN totals sum correctly across the organizer\'s own events only')]
    public function it_scopes_the_aggregate_summary_to_the_organizers_own_events(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $otherOrganizer = Organizer::factory()->approved()->create();

        $eventOne = Event::factory()->for($organizer, 'organizer')->create();
        $eventTwo = Event::factory()->for($organizer, 'organizer')->create();
        $otherEvent = Event::factory()->for($otherOrganizer, 'organizer')->create();

        EventStats::factory()->for($eventOne)->create(['views_count' => 10, 'favorites_count' => 1, 'ticket_link_clicks_count' => 0, 'interest_count' => 2]);
        EventStats::factory()->for($eventTwo)->create(['views_count' => 30, 'favorites_count' => 3, 'ticket_link_clicks_count' => 1, 'interest_count' => 4]);
        EventStats::factory()->for($otherEvent)->create(['views_count' => 999, 'favorites_count' => 999, 'ticket_link_clicks_count' => 999, 'interest_count' => 999]);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->getJson('/api/admin/v1/organizer/engagement');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['eventId' => $eventOne->id, 'viewsCount' => 10]);
        $response->assertJsonFragment(['eventId' => $eventTwo->id, 'viewsCount' => 30]);
        $response->assertJsonMissing(['eventId' => $otherEvent->id]);
    }

    #[Test]
    #[TestDox('IF an event has zero recorded activity THEN the system SHALL show explicit zero-value stats rather than omitting the event')]
    public function it_zero_fills_events_with_no_event_stats_row(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $eventWithStats = Event::factory()->for($organizer, 'organizer')->create();
        $eventWithoutStats = Event::factory()->for($organizer, 'organizer')->create();
        EventStats::factory()->for($eventWithStats)->create(['views_count' => 5, 'favorites_count' => 1, 'ticket_link_clicks_count' => 1, 'interest_count' => 1]);

        $summaryResponse = $this->actingAsApprovedOrganizer($organizer)
            ->getJson('/api/admin/v1/organizer/engagement');

        $summaryResponse->assertOk();
        $summaryResponse->assertJsonCount(2, 'data');
        $summaryResponse->assertJsonFragment([
            'eventId' => $eventWithoutStats->id,
            'viewsCount' => 0,
            'favoritesCount' => 0,
            'ticketLinkClicksCount' => 0,
            'interestCount' => 0,
        ]);

        $showResponse = $this->getJson("/api/admin/v1/organizer/events/{$eventWithoutStats->id}/engagement");

        $showResponse->assertOk();
        $showResponse->assertJsonFragment([
            'eventId' => $eventWithoutStats->id,
            'viewsCount' => 0,
            'favoritesCount' => 0,
            'ticketLinkClicksCount' => 0,
            'interestCount' => 0,
        ]);
    }

    #[Test]
    #[TestDox('GIVEN organizer A\'s event WHEN organizer B requests its engagement THEN the response is 403')]
    public function it_denies_showing_engagement_for_another_organizers_event(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizerA, 'organizer')->create();
        EventStats::factory()->for($event)->create();

        $response = $this->actingAsApprovedOrganizer($organizerB)
            ->getJson("/api/admin/v1/organizer/events/{$event->id}/engagement");

        $response->assertForbidden();
    }

    private function actingAsApprovedOrganizer(Organizer $organizer): self
    {
        $this->withSession([
            AdminPanelConstants::ORGANIZER_APPROVAL_STATE_SESSION_KEY => OrganizerApprovalState::Approved->value,
        ])->actingAs($organizer, 'organizer');

        return $this;
    }
}
