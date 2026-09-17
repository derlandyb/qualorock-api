<?php

namespace Tests\Feature\Organizer;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Enums\OrganizerApprovalState;
use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\EventInfoRequest;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class EventInfoRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Referer', 'http://localhost');
    }

    #[Test]
    #[TestDox('GIVEN a consumer submits an info request WHEN the organizer lists their event\'s requests THEN it appears')]
    public function it_lists_info_requests_for_the_organizers_own_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->create();
        $infoRequest = EventInfoRequest::factory()->for($event)->create(['message' => 'What time does it start?']);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->getJson("/api/admin/v1/organizer/events/{$event->id}/info-requests");

        $response->assertOk();
        $response->assertJsonFragment(['id' => $infoRequest->id, 'message' => 'What time does it start?']);
    }

    #[Test]
    #[TestDox('GIVEN two of the organizer\'s events with their own info requests WHEN listing one event\'s requests THEN the other event\'s requests are excluded')]
    public function it_scopes_the_list_to_only_the_requested_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $eventOne = Event::factory()->for($organizer, 'organizer')->create();
        $eventTwo = Event::factory()->for($organizer, 'organizer')->create();
        EventInfoRequest::factory()->for($eventOne)->create();
        $otherEventRequest = EventInfoRequest::factory()->for($eventTwo)->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->getJson("/api/admin/v1/organizer/events/{$eventOne->id}/info-requests");

        $response->assertOk();
        $response->assertJsonMissing(['id' => $otherEventRequest->id]);
    }

    #[Test]
    #[TestDox('GIVEN organizer A\'s event WHEN organizer B attempts to list its info requests THEN the response is 403')]
    public function it_denies_listing_info_requests_for_another_organizers_event(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizerA, 'organizer')->create();
        EventInfoRequest::factory()->for($event)->create();

        $response = $this->actingAsApprovedOrganizer($organizerB)
            ->getJson("/api/admin/v1/organizer/events/{$event->id}/info-requests");

        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('GIVEN an organizer responds to a request THEN the response and timestamp are stored and returned on subsequent reads')]
    public function it_stores_the_organizers_response_and_returns_it_on_subsequent_reads(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->create();
        $infoRequest = EventInfoRequest::factory()->for($event)->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/info-requests/{$infoRequest->id}/respond", [
                'response' => 'Doors open at 8pm.',
            ]);

        $response->assertOk();
        $response->assertJsonFragment(['organizerResponse' => 'Doors open at 8pm.']);
        $this->assertNotNull($response->json('data.respondedAt'));

        $listResponse = $this->getJson("/api/admin/v1/organizer/events/{$event->id}/info-requests");
        $listResponse->assertJsonFragment(['organizerResponse' => 'Doors open at 8pm.']);
    }

    #[Test]
    #[TestDox('GIVEN organizer A WHEN attempting to respond to organizer B\'s event info request THEN the response is 403 and no response is stored')]
    public function it_denies_responding_to_another_organizers_info_request(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizerB, 'organizer')->create();
        $infoRequest = EventInfoRequest::factory()->for($event)->create();

        $response = $this->actingAsApprovedOrganizer($organizerA)
            ->postJson("/api/admin/v1/organizer/info-requests/{$infoRequest->id}/respond", [
                'response' => 'Hijacked response',
            ]);

        $response->assertForbidden();
        $this->assertNull($infoRequest->fresh()->organizer_response);
    }

    private function actingAsApprovedOrganizer(Organizer $organizer): self
    {
        $this->withSession([
            AdminPanelConstants::ORGANIZER_APPROVAL_STATE_SESSION_KEY => OrganizerApprovalState::Approved->value,
        ])->actingAs($organizer, 'organizer');

        return $this;
    }
}
