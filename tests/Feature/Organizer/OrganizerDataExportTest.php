<?php

namespace Tests\Feature\Organizer;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Enums\DataExportRequestStatus;
use App\Domain\Enums\OrganizerApprovalState;
use App\Infrastructure\Jobs\GenerateOrganizerDataExportJob;
use App\Infrastructure\Persistence\Eloquent\DataExportRequest;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionObject;
use Tests\TestCase;

class OrganizerDataExportTest extends TestCase
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
    #[TestDox('GIVEN an approved organizer WHEN requesting a data export THEN a pending request is created and the export job is queued')]
    public function it_creates_a_pending_export_request_and_queues_the_job(): void
    {
        Queue::fake();
        $organizer = Organizer::factory()->approved()->create();

        $response = $this->actingAsApprovedOrganizer($organizer)->postJson('/api/admin/v1/organizer/data-export');

        $response->assertStatus(202);
        $response->assertJsonFragment(['status' => DataExportRequestStatus::Pending->value]);
        $requestId = $response->json('data.id');
        $this->assertDatabaseHas('data_export_requests', [
            'id' => $requestId,
            'organizer_id' => $organizer->id,
            'status' => DataExportRequestStatus::Pending->value,
        ]);
        Queue::assertPushed(
            GenerateOrganizerDataExportJob::class,
            fn (GenerateOrganizerDataExportJob $job) => $this->jobTargets($job, $organizer->id, $requestId),
        );
    }

    #[Test]
    #[TestDox('GIVEN a ready export owned by the organizer WHEN fetching its status THEN a freshly signed download URL is returned')]
    public function it_returns_a_signed_download_url_for_a_ready_export_it_owns(): void
    {
        Storage::fake('s3');
        Storage::disk('s3')->put('organizer-exports/abc123.json', '{}');
        $organizer = Organizer::factory()->approved()->create();
        $exportRequest = DataExportRequest::factory()->for($organizer, 'organizer')->create([
            'status' => DataExportRequestStatus::Ready,
            'download_path' => 'organizer-exports/abc123.json',
        ]);

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->getJson("/api/admin/v1/organizer/data-export/{$exportRequest->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', DataExportRequestStatus::Ready->value);
        $this->assertNotNull($response->json('data.downloadUrl'));
    }

    #[Test]
    #[TestDox("GIVEN another organizer's export WHEN fetching its status THEN it is not found")]
    public function it_denies_access_to_another_organizers_export(): void
    {
        $organizer = Organizer::factory()->approved()->create();
        $otherOrganizer = Organizer::factory()->approved()->create();
        $exportRequest = DataExportRequest::factory()->for($otherOrganizer, 'organizer')->create();

        $response = $this->actingAsApprovedOrganizer($organizer)
            ->getJson("/api/admin/v1/organizer/data-export/{$exportRequest->id}");

        $response->assertStatus(404);
    }

    private function actingAsApprovedOrganizer(Organizer $organizer): self
    {
        $this->withSession([
            AdminPanelConstants::ORGANIZER_APPROVAL_STATE_SESSION_KEY => OrganizerApprovalState::Approved->value,
        ])->actingAs($organizer, 'organizer');

        return $this;
    }

    private function jobTargets(GenerateOrganizerDataExportJob $job, int $organizerId, int $requestId): bool
    {
        $reflection = new ReflectionObject($job);
        $actualOrganizerId = $reflection->getProperty('organizerId')->getValue($job);
        $actualRequestId = $reflection->getProperty('dataExportRequestId')->getValue($job);

        return $actualOrganizerId === $organizerId && $actualRequestId === $requestId;
    }
}
