<?php

namespace Tests\Feature\Organizer;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Enums\OrganizerApprovalState;
use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum only starts a session for a request whose Referer/Origin
        // matches a configured stateful domain - without this header
        // `$request->session()` throws in EnsureOrganizerApproved.
        $this->withHeader('Referer', 'http://localhost');
    }

    #[Test]
    #[TestDox('GIVEN all required fields WHEN creating an event THEN it saves as draft')]
    public function it_creates_an_event_as_draft(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $venue = Venue::factory()->for($organizer)->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson('/api/admin/v1/organizer/events', $this->validEventPayload($venue->id));

        $response->assertCreated();
        $response->assertJsonFragment(['status' => 'draft']);
        $this->assertDatabaseHas('events', [
            'organizer_id' => $organizer->id,
            'venue_id' => $venue->id,
            'status' => 'draft',
        ]);
    }

    #[Test]
    #[TestDox('GIVEN an organizer edits their own event WHEN saving THEN the changes apply immediately')]
    public function it_updates_an_organizers_own_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->create(['title' => 'Old Title']);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->putJson("/api/admin/v1/organizer/events/{$event->id}", ['title' => 'New Title']);

        $response->assertOk();
        $this->assertSame('New Title', $event->fresh()->title);
    }

    #[Test]
    #[TestDox('GIVEN organizer A WHEN attempting to update organizer B\'s event THEN the response is 403')]
    public function it_denies_updating_another_organizers_event(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizerB, 'organizer')->create();

        $response = $this->actingAsApprovedOrganizer($organizerA)
            ->putJson("/api/admin/v1/organizer/events/{$event->id}", ['title' => 'Hijacked']);

        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('GIVEN an invalid status transition WHEN requested THEN the response is 422')]
    public function it_rejects_an_invalid_status_transition(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->cancelled()->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/events/{$event->id}/status", ['status' => 'published']);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'invalid_transition']);
    }

    #[Test]
    #[TestDox('GIVEN an organizer deletes their own event WHEN removed THEN it no longer exists')]
    public function it_deletes_an_organizers_own_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->deleteJson("/api/admin/v1/organizer/events/{$event->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    #[Test]
    #[TestDox('GIVEN an organizer duplicates their own event WHEN duplicated THEN a new draft copy is created with the same field values')]
    public function it_duplicates_an_organizers_own_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->published()->create(['title' => 'Original']);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/events/{$event->id}/duplicate");

        $response->assertCreated();
        $response->assertJsonFragment(['title' => 'Original', 'status' => 'draft']);
        $this->assertDatabaseCount('events', 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function validEventPayload(int $venueId): array
    {
        return [
            'venueId' => $venueId,
            'title' => 'Rock Night',
            'description' => 'A great rock night.',
            'dateTime' => now()->addWeek()->toIso8601String(),
            'location' => 'Downtown',
            'fullAddress' => '123 Main St',
            'featuredImageUrl' => 'https://example.com/image.jpg',
            'externalTicketLink' => 'https://example.com/tickets',
            'priceType' => 'paid',
            'musicCategory' => 'Rock',
        ];
    }

    private function actingAsApprovedOrganizer(Organizer $organizer): self
    {
        $this->withSession([
            AdminPanelConstants::ORGANIZER_APPROVAL_STATE_SESSION_KEY => OrganizerApprovalState::Approved->value,
        ])->actingAs($organizer, 'organizer');

        return $this;
    }
}
