<?php

namespace Tests\Feature\Organizer;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Enums\OrganizerApprovalState;
use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\Promoter;
use App\Infrastructure\Persistence\Eloquent\SuperAdmin;
use App\Infrastructure\Persistence\Eloquent\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class OrganizerDataDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Referer', 'http://localhost');
    }

    #[Test]
    #[TestDox('GIVEN an organizer with no upcoming published events WHEN requesting deletion THEN the account is soft-deleted and its venue, events, and promoters are hidden')]
    public function it_soft_deletes_and_hides_data_when_there_is_no_upcoming_published_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $venue = Venue::factory()->for($organizer)->create();
        $event = Event::factory()->for($organizer, 'organizer')->for($venue)->draft()->create();
        $promoter = Promoter::factory()->for($organizer)->create();

        $response = $this->actingAsApprovedOrganizer($organizer)->postJson('/api/admin/v1/organizer/account/delete');

        $response->assertStatus(204);
        $this->assertSoftDeleted('organizers', ['id' => $organizer->id]);
        $this->assertNotNull($venue->fresh()->hidden_at);
        $this->assertNotNull($event->fresh()->hidden_at);
        $this->assertNotNull($promoter->fresh()->hidden_at);
    }

    #[Test]
    #[TestDox('GIVEN an organizer with an upcoming published event WHEN requesting deletion without confirm THEN the response is 409')]
    public function it_requires_confirmation_when_there_is_an_upcoming_published_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $venue = Venue::factory()->for($organizer)->create();
        Event::factory()->for($organizer, 'organizer')->for($venue)->published()->create([
            'date_time' => now()->addWeek(),
        ]);

        $response = $this->actingAsApprovedOrganizer($organizer)->postJson('/api/admin/v1/organizer/account/delete');

        $response->assertStatus(409);
        $response->assertJsonFragment(['error' => 'confirmation_required']);
        $this->assertDatabaseHas('organizers', ['id' => $organizer->id, 'deleted_at' => null]);
    }

    #[Test]
    #[TestDox('GIVEN an organizer with an upcoming published event WHEN requesting deletion with confirm true THEN it succeeds')]
    public function it_deletes_with_confirmation_despite_an_upcoming_published_event(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $venue = Venue::factory()->for($organizer)->create();
        Event::factory()->for($organizer, 'organizer')->for($venue)->published()->create([
            'date_time' => now()->addWeek(),
        ]);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson('/api/admin/v1/organizer/account/delete', ['confirm' => true]);

        $response->assertStatus(204);
        $this->assertSoftDeleted('organizers', ['id' => $organizer->id]);
    }

    #[Test]
    #[TestDox("GIVEN a super_admin WHEN triggering deletion on an organizer's behalf THEN the same flow applies")]
    public function it_allows_a_super_admin_to_delete_on_the_organizers_behalf(): void
    {
        $superAdmin = SuperAdmin::factory()->create();
        $organizer = Organizer::factory()->approved()->create();
        $venue = Venue::factory()->for($organizer)->create();

        $response = $this->actingAs($superAdmin, 'super_admin')
            ->postJson("/api/admin/v1/super-admin/organizers/{$organizer->id}/delete");

        $response->assertStatus(204);
        $this->assertSoftDeleted('organizers', ['id' => $organizer->id]);
        $this->assertNotNull($venue->fresh()->hidden_at);
    }

    #[Test]
    #[TestDox('GIVEN a non-super-admin WHEN calling the super-admin deletion override THEN the response is 403')]
    public function it_denies_a_non_super_admin_on_the_override_route(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $otherOrganizer = Organizer::factory()->approved()->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/super-admin/organizers/{$otherOrganizer->id}/delete");

        $response->assertForbidden();
        $this->assertDatabaseHas('organizers', ['id' => $otherOrganizer->id, 'deleted_at' => null]);
    }

    private function actingAsApprovedOrganizer(Organizer $organizer): self
    {
        $this->withSession([
            AdminPanelConstants::ORGANIZER_APPROVAL_STATE_SESSION_KEY => OrganizerApprovalState::Approved->value,
        ])->actingAs($organizer, 'organizer');

        return $this;
    }
}
