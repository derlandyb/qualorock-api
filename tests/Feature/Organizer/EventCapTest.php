<?php

namespace Tests\Feature\Organizer;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Enums\OrganizerApprovalState;
use App\Domain\Enums\PlanTier;
use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class EventCapTest extends TestCase
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
    #[TestDox('GIVEN a Basic-tier organizer with 4 published events this month WHEN publishing a 5th THEN the response is 422 with upgrade_required')]
    public function it_blocks_a_fifth_publish_for_a_basic_tier_organizer(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        Event::factory()->for($organizer, 'organizer')->published()->count(4)->create();
        $draft = Event::factory()->for($organizer, 'organizer')->draft()->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/events/{$draft->id}/status", ['status' => 'published']);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'upgrade_required']);
    }

    #[Test]
    #[TestDox('GIVEN a Plus-tier organizer with 4+ published events this month WHEN publishing another THEN it succeeds')]
    public function it_allows_a_plus_tier_organizer_past_the_basic_limit(): void
    {
        $organizer = Organizer::factory()->approved()->create(['plan_tier' => PlanTier::Plus]);
        Event::factory()->for($organizer, 'organizer')->published()->count(4)->create();
        $draft = Event::factory()->for($organizer, 'organizer')->draft()->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->postJson("/api/admin/v1/organizer/events/{$draft->id}/status", ['status' => 'published']);

        $response->assertOk();
        $response->assertJsonFragment(['status' => 'published']);
    }

    private function actingAsApprovedOrganizer(Organizer $organizer): self
    {
        $this->withSession([
            AdminPanelConstants::ORGANIZER_APPROVAL_STATE_SESSION_KEY => OrganizerApprovalState::Approved->value,
        ])->actingAs($organizer, 'organizer');

        return $this;
    }
}
