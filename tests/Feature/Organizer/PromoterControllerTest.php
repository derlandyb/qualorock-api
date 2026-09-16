<?php

namespace Tests\Feature\Organizer;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Enums\OrganizerApprovalState;
use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\Promoter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class PromoterControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Referer', 'http://localhost');
    }

    #[Test]
    #[TestDox('GIVEN valid promoter fields WHEN registering a promoter THEN it saves scoped to the organizer')]
    public function it_registers_a_promoter_for_the_organizer(): void
    {
        $organizer = Organizer::factory()->approved()->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson('/api/admin/v1/organizer/promoters', $this->validPromoterPayload());

        $response->assertCreated();
        $this->assertDatabaseHas('promoters', [
            'organizer_id' => $organizer->id,
            'name' => 'DJ Test',
        ]);
    }

    #[Test]
    #[TestDox('GIVEN a promoter and an event owned by the same organizer WHEN linking them THEN the event\'s promoter list includes the promoter')]
    public function it_links_a_promoter_to_the_organizers_own_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $promoter = Promoter::factory()->for($organizer)->create();
        $event = Event::factory()->for($organizer, 'organizer')->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/promoters/{$promoter->id}/events/{$event->id}");

        $response->assertNoContent();

        $listResponse = $this->getJson("/api/admin/v1/organizer/events/{$event->id}/promoters");
        $listResponse->assertOk();
        $listResponse->assertJsonFragment(['id' => $promoter->id]);
    }

    #[Test]
    #[TestDox('GIVEN organizer A\'s promoter and organizer B\'s event WHEN attempting to link them THEN the response is 403 and no pivot row is created')]
    public function it_denies_linking_across_organizers(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $promoterA = Promoter::factory()->for($organizerA)->create();
        $eventB = Event::factory()->for($organizerB, 'organizer')->create();

        $response = $this->actingAsApprovedOrganizer($organizerA)
            ->postJson("/api/admin/v1/organizer/promoters/{$promoterA->id}/events/{$eventB->id}");

        $response->assertForbidden();
        $this->assertDatabaseMissing('event_promoter', [
            'promoter_id' => $promoterA->id,
            'event_id' => $eventB->id,
        ]);
    }

    #[Test]
    #[TestDox('GIVEN an event\'s promoter list WHEN requested THEN every currently-linked promoter is returned')]
    public function it_returns_every_promoter_linked_to_an_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $event = Event::factory()->for($organizer, 'organizer')->create();
        $promoterOne = Promoter::factory()->for($organizer)->create();
        $promoterTwo = Promoter::factory()->for($organizer)->create();
        $event->promoters()->attach([$promoterOne->id, $promoterTwo->id]);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->getJson("/api/admin/v1/organizer/events/{$event->id}/promoters");

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    #[TestDox('GIVEN a promoter linked to 3 events WHEN the organizer edits that promoter THEN all 3 events reflect the change')]
    public function it_propagates_promoter_edits_to_every_linked_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $promoter = Promoter::factory()->for($organizer)->create(['name' => 'Old Name']);
        $events = Event::factory()->for($organizer, 'organizer')->count(3)->create();
        foreach ($events as $event) {
            $event->promoters()->attach($promoter->id);
        }

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->putJson("/api/admin/v1/organizer/promoters/{$promoter->id}", ['name' => 'New Name']);

        $response->assertOk();

        foreach ($events as $event) {
            $listResponse = $this->getJson("/api/admin/v1/organizer/events/{$event->id}/promoters");
            $listResponse->assertJsonFragment(['name' => 'New Name']);
        }
    }

    #[Test]
    #[TestDox('GIVEN a promoter linked to a published event WHEN the organizer removes the promoter THEN the event\'s promoter list no longer includes them but the event itself is untouched')]
    public function it_removes_a_deleted_promoter_from_events_without_deleting_the_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $promoter = Promoter::factory()->for($organizer)->create();
        $event = Event::factory()->for($organizer, 'organizer')->published()->create();
        $event->promoters()->attach($promoter->id);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->deleteJson("/api/admin/v1/organizer/promoters/{$promoter->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('promoters', ['id' => $promoter->id]);
        $this->assertDatabaseMissing('event_promoter', ['promoter_id' => $promoter->id]);
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    #[Test]
    #[TestDox('GIVEN organizer A WHEN attempting to update organizer B\'s promoter THEN the response is 403')]
    public function it_denies_updating_another_organizers_promoter(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $promoter = Promoter::factory()->for($organizerB)->create(['name' => 'Original']);

        $response = $this->actingAsApprovedOrganizer($organizerA)
            ->putJson("/api/admin/v1/organizer/promoters/{$promoter->id}", ['name' => 'Hijacked']);

        $response->assertForbidden();
        $this->assertSame('Original', $promoter->fresh()->name);
    }

    #[Test]
    #[TestDox('GIVEN organizer A WHEN attempting to delete organizer B\'s promoter THEN the response is 403')]
    public function it_denies_deleting_another_organizers_promoter(): void
    {
        $organizerA = Organizer::factory()->approved()->create();
        $organizerB = Organizer::factory()->approved()->create();
        $promoter = Promoter::factory()->for($organizerB)->create();

        $response = $this->actingAsApprovedOrganizer($organizerA)
            ->deleteJson("/api/admin/v1/organizer/promoters/{$promoter->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('promoters', ['id' => $promoter->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPromoterPayload(): array
    {
        return [
            'name' => 'DJ Test',
            'phone' => '+55 11 91234-5678',
            'email' => 'dj@example.com',
            'instagramUrl' => 'https://instagram.com/djtest',
            'tiktokUrl' => 'https://tiktok.com/@djtest',
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
