<?php

namespace Tests\Feature\Organizer;

use App\Domain\Enums\OrganizerApprovalState;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class OrganizerLoginGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum only treats a request as stateful (session-backed) when its
        // Referer/Origin matches a configured stateful domain - without this
        // header the test client's requests never get a session store bound.
        $this->withHeader('Referer', 'http://localhost');

        Route::middleware(['api', 'auth:organizer', 'organizer.approved'])
            ->get('/api/admin/v1/organizer/__management-check', fn () => response()->json(['ok' => true]));
    }

    #[Test]
    #[TestDox('GIVEN a pending organizer WHEN they log in and reach a management route THEN it returns 403 with their current state')]
    public function it_denies_a_pending_organizer_management_access(): void
    {
        Organizer::factory()->pending()->create(['email' => 'pending@example.com']);

        $this->postJson('/api/admin/v1/organizer/login', [
            'email' => 'pending@example.com',
            'password' => 'password',
        ])->assertOk();

        $response = $this->getJson('/api/admin/v1/organizer/__management-check');

        $response->assertForbidden();
        $response->assertJson(['state' => 'pending', 'rejectionReason' => null]);
    }

    #[Test]
    #[TestDox('GIVEN a rejected organizer WHEN they log in and reach a management route THEN it returns 403 with the stored rejection reason')]
    public function it_denies_a_rejected_organizer_management_access_with_its_reason(): void
    {
        Organizer::factory()->rejected()->create([
            'email' => 'rejected@example.com',
            'rejection_reason' => 'Missing venue documentation.',
        ]);

        $this->postJson('/api/admin/v1/organizer/login', [
            'email' => 'rejected@example.com',
            'password' => 'password',
        ])->assertOk();

        $response = $this->getJson('/api/admin/v1/organizer/__management-check');

        $response->assertForbidden();
        $response->assertJson([
            'state' => 'rejected',
            'rejectionReason' => 'Missing venue documentation.',
        ]);
    }

    #[Test]
    #[TestDox('GIVEN an approved organizer WHEN they log in THEN a management route is reachable')]
    public function it_allows_an_approved_organizer_management_access(): void
    {
        Organizer::factory()->approved()->create(['email' => 'approved@example.com']);

        $this->postJson('/api/admin/v1/organizer/login', [
            'email' => 'approved@example.com',
            'password' => 'password',
        ])->assertOk();

        $this->getJson('/api/admin/v1/organizer/__management-check')->assertOk();
    }

    #[Test]
    #[TestDox('GIVEN an organizer approved while their session is still active WHEN they reach a management route THEN it still returns 403 until they log in again')]
    public function it_requires_re_authentication_after_a_mid_session_approval(): void
    {
        $organizer = Organizer::factory()->pending()->create(['email' => 'midsession@example.com']);

        $this->postJson('/api/admin/v1/organizer/login', [
            'email' => 'midsession@example.com',
            'password' => 'password',
        ])->assertOk();

        $organizer->update(['approval_state' => OrganizerApprovalState::Approved]);

        $this->getJson('/api/admin/v1/organizer/__management-check')->assertForbidden();

        $this->postJson('/api/admin/v1/organizer/login', [
            'email' => 'midsession@example.com',
            'password' => 'password',
        ])->assertOk();

        $this->getJson('/api/admin/v1/organizer/__management-check')->assertOk();
    }
}
