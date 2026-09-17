<?php

namespace Tests\Feature\Jobs;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Contracts\DataExportRequestRepositoryInterface;
use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Contracts\OrganizerRepositoryInterface;
use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Contracts\VenueRepositoryInterface;
use App\Domain\Enums\DataExportRequestStatus;
use App\Infrastructure\Jobs\GenerateOrganizerDataExportJob;
use App\Infrastructure\Persistence\Eloquent\DataExportRequest;
use App\Infrastructure\Persistence\Eloquent\Event;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use App\Infrastructure\Persistence\Eloquent\Promoter;
use App\Infrastructure\Persistence\Eloquent\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class GenerateOrganizerDataExportJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestDox('GIVEN an organizer with a venue, events, and promoters WHEN the export job runs THEN the request becomes ready with a download URL')]
    public function it_marks_the_request_ready_with_a_download_url(): void
    {
        Storage::fake('s3');
        $organizer = Organizer::factory()->approved()->create();
        Venue::factory()->for($organizer, 'organizer')->create();
        Event::factory()->for($organizer, 'organizer')->count(2)->create();
        Promoter::factory()->for($organizer, 'organizer')->create();
        $exportRequest = DataExportRequest::factory()->for($organizer, 'organizer')->create();

        (new GenerateOrganizerDataExportJob($organizer->id, $exportRequest->id))->handle(
            app(OrganizerRepositoryInterface::class),
            app(VenueRepositoryInterface::class),
            app(EventRepositoryInterface::class),
            app(PromoterRepositoryInterface::class),
            app(DataExportRequestRepositoryInterface::class),
        );

        $exportRequest->refresh();
        $this->assertSame(DataExportRequestStatus::Ready, $exportRequest->status);
        $this->assertNotNull($exportRequest->download_url);
        $files = Storage::disk('s3')->files(AdminPanelConstants::DATA_EXPORT_STORAGE_DIRECTORY);
        $this->assertCount(1, $files);
    }

    #[Test]
    #[TestDox("GIVEN two organizers' data WHEN one organizer's export job runs THEN the archive contains only that organizer's own rows")]
    public function it_scopes_the_archive_to_the_target_organizer_only(): void
    {
        Storage::fake('s3');
        $organizer = Organizer::factory()->approved()->create();
        $otherOrganizer = Organizer::factory()->approved()->create();
        Venue::factory()->for($organizer, 'organizer')->create(['name' => 'Own Venue']);
        Venue::factory()->for($otherOrganizer, 'organizer')->create(['name' => 'Other Venue']);
        $ownEvent = Event::factory()->for($organizer, 'organizer')->create(['title' => 'Own Event']);
        Event::factory()->for($otherOrganizer, 'organizer')->create(['title' => 'Other Event']);
        $ownPromoter = Promoter::factory()->for($organizer, 'organizer')->create(['name' => 'Own Promoter']);
        Promoter::factory()->for($otherOrganizer, 'organizer')->create(['name' => 'Other Promoter']);
        $exportRequest = DataExportRequest::factory()->for($organizer, 'organizer')->create();

        (new GenerateOrganizerDataExportJob($organizer->id, $exportRequest->id))->handle(
            app(OrganizerRepositoryInterface::class),
            app(VenueRepositoryInterface::class),
            app(EventRepositoryInterface::class),
            app(PromoterRepositoryInterface::class),
            app(DataExportRequestRepositoryInterface::class),
        );

        $files = Storage::disk('s3')->files(AdminPanelConstants::DATA_EXPORT_STORAGE_DIRECTORY);
        $archive = json_decode(Storage::disk('s3')->get($files[0]), true);

        $this->assertSame('Own Venue', $archive['venue']['name']);
        $this->assertCount(1, $archive['events']);
        $this->assertSame('Own Event', $archive['events'][0]['title']);
        $this->assertCount(1, $archive['promoters']);
        $this->assertSame('Own Promoter', $archive['promoters'][0]['name']);
        $this->assertSame($organizer->id, $archive['organizer']['id']);
        $this->assertNotSame($ownEvent->organizer_id, $otherOrganizer->id);
        $this->assertNotSame($ownPromoter->organizer_id, $otherOrganizer->id);
    }
}
