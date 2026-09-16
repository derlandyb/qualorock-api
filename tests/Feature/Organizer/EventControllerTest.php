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
    #[TestDox('GIVEN a published event WHEN transitioning to cancelled THEN it succeeds')]
    public function it_allows_transitioning_a_published_event_to_cancelled(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->published()->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/events/{$event->id}/status", ['status' => 'cancelled']);

        $response->assertOk();
        $response->assertJsonFragment(['status' => 'cancelled']);
    }

    #[Test]
    #[TestDox('GIVEN a published event WHEN transitioning to closed THEN it succeeds')]
    public function it_allows_transitioning_a_published_event_to_closed(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->published()->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/events/{$event->id}/status", ['status' => 'closed']);

        $response->assertOk();
        $response->assertJsonFragment(['status' => 'closed']);
    }

    #[Test]
    #[TestDox('GIVEN a draft event WHEN transitioning to cancelled THEN it succeeds')]
    public function it_allows_transitioning_a_draft_event_to_cancelled(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->draft()->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/events/{$event->id}/status", ['status' => 'cancelled']);

        $response->assertOk();
        $response->assertJsonFragment(['status' => 'cancelled']);
    }

    #[Test]
    #[TestDox('GIVEN a required field is missing WHEN attempting to transition to published THEN the response is 422 with the missing fields identified')]
    public function it_rejects_publishing_an_event_with_a_missing_required_field_over_http(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->draft()->create(['location' => '']);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/events/{$event->id}/status", ['status' => 'published']);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'missing_required_fields']);
        $response->assertJsonFragment(['missingFields' => ['location']]);
    }

    #[Test]
    #[TestDox('GIVEN organizer A WHEN attempting to delete organizer B\'s event THEN the response is 403')]
    public function it_denies_deleting_another_organizers_event(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizerB, 'organizer')->create();

        $response = $this->actingAsApprovedOrganizer($organizerA)
            ->deleteJson("/api/admin/v1/organizer/events/{$event->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    #[Test]
    #[TestDox('GIVEN organizer A WHEN attempting to duplicate organizer B\'s event THEN the response is 403')]
    public function it_denies_duplicating_another_organizers_event(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizerB, 'organizer')->create();

        $response = $this->actingAsApprovedOrganizer($organizerA)
            ->postJson("/api/admin/v1/organizer/events/{$event->id}/duplicate");

        $response->assertForbidden();
        $this->assertDatabaseCount('events', 1);
    }

    #[Test]
    #[TestDox('GIVEN organizer A WHEN attempting to transition organizer B\'s event status THEN the response is 403')]
    public function it_denies_transitioning_another_organizers_event_status(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizerB, 'organizer')->draft()->create();

        $response = $this->actingAsApprovedOrganizer($organizerA)
            ->postJson("/api/admin/v1/organizer/events/{$event->id}/status", ['status' => 'published']);

        $response->assertForbidden();
        $this->assertSame('draft', $event->fresh()->status->value);
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
        $venue = Venue::factory()->for($organizer)->create();
        $event = Event::factory()->for($organizer, 'organizer')->for($venue)->published()->create([
            'title' => 'Original',
            'description' => 'Original description',
            'location' => 'Original location',
            'full_address' => 'Original address',
            'featured_image_url' => 'https://example.com/original.jpg',
            'external_ticket_link' => 'https://example.com/original-tickets',
            'music_category' => 'Jazz',
            'capacity' => 100,
            'age_range' => '18+',
            'additional_info' => 'Bring ID',
            'accessibility_info' => 'Wheelchair accessible',
            'event_rules' => 'No smoking',
        ]);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/events/{$event->id}/duplicate");

        $response->assertCreated();
        $response->assertJsonFragment([
            'venueId' => $venue->id,
            'title' => 'Original',
            'description' => 'Original description',
            'location' => 'Original location',
            'fullAddress' => 'Original address',
            'featuredImageUrl' => 'https://example.com/original.jpg',
            'externalTicketLink' => 'https://example.com/original-tickets',
            'musicCategory' => 'Jazz',
            'capacity' => 100,
            'ageRange' => '18+',
            'additionalInfo' => 'Bring ID',
            'accessibilityInfo' => 'Wheelchair accessible',
            'eventRules' => 'No smoking',
            'status' => 'draft',
            'publishedAt' => null,
        ]);
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
