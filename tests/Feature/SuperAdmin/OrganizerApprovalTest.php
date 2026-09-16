<?php

namespace Tests\Feature\SuperAdmin;

use App\Domain\Enums\OrganizerApprovalState;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class OrganizerApprovalTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('GIVEN pending organizers WHEN a super admin lists them THEN signup details are returned')]
    public function it_lists_pending_organizers_with_their_signup_details(): void
    {
        $superAdmin = SuperAdmin::factory()->create();
        $pending = Organizer::factory()->pending()->create();
        Organizer::factory()->approved()->create();

        $response = $this->actingAs($superAdmin, 'super_admin')->getJson('/api/admin/v1/super-admin/organizers');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $pending->id,
            'orgName' => $pending->org_name,
            'email' => $pending->email,
            'approvalState' => 'pending',
        ]);
    }

    #[Test]
    #[TestDox('GIVEN a pending organizer WHEN a super admin approves it THEN its state becomes approved')]
    public function it_approves_a_pending_organizer(): void
    {
        $superAdmin = SuperAdmin::factory()->create();
        $organizer = Organizer::factory()->pending()->create();

        $response = $this->actingAs($superAdmin, 'super_admin')
            ->postJson("/api/admin/v1/super-admin/organizers/{$organizer->id}/approve");

        $response->assertOk();
        $this->assertSame(OrganizerApprovalState::Approved, $organizer->fresh()->approval_state);
    }

    #[Test]
    #[TestDox('GIVEN a pending organizer WHEN a super admin rejects it with a reason THEN its state becomes rejected and the reason is stored')]
    public function it_rejects_a_pending_organizer_with_a_reason(): void
    {
        $superAdmin = SuperAdmin::factory()->create();
        $organizer = Organizer::factory()->pending()->create();

        $response = $this->actingAs($superAdmin, 'super_admin')
            ->postJson("/api/admin/v1/super-admin/organizers/{$organizer->id}/reject", [
                'reason' => 'Missing venue documentation.',
            ]);

        $response->assertOk();
        $fresh = $organizer->fresh();
        $this->assertSame(OrganizerApprovalState::Rejected, $fresh->approval_state);
        $this->assertSame('Missing venue documentation.', $fresh->rejection_reason);
    }

    #[Test]
    #[TestDox('GIVEN an organizer, not a super admin, WHEN calling the approval endpoints THEN the response is 403')]
    public function it_denies_a_non_super_admin(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $target = Organizer::factory()->pending()->create();

        $response = $this->actingAs($organizer, 'organizer')->getJson('/api/admin/v1/super-admin/organizers');
        $response->assertForbidden();

        $response = $this->actingAs($organizer, 'organizer')
            ->postJson("/api/admin/v1/super-admin/organizers/{$target->id}/approve");
        $response->assertForbidden();

        $response = $this->actingAs($organizer, 'organizer')
            ->postJson("/api/admin/v1/super-admin/organizers/{$target->id}/reject");
        $response->assertForbidden();
    }

    #[Test]
    #[TestDox('GIVEN an organizer, not a super admin, WHEN rejecting with an invalid payload THEN the response is still 403, not a validation error')]
    public function it_denies_a_non_super_admin_before_validating_the_reject_payload(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $target = Organizer::factory()->pending()->create();

        $response = $this->actingAs($organizer, 'organizer')
            ->postJson("/api/admin/v1/super-admin/organizers/{$target->id}/reject", [
                'reason' => str_repeat('x', 1001),
            ]);

        $response->assertForbidden();
    }
}
