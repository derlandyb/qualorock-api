<?php

namespace App\Infrastructure\Jobs;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Contracts\DataExportRequestRepositoryInterface;
use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Contracts\OrganizerRepositoryInterface;
use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Contracts\VenueRepositoryInterface;
use App\Domain\Entities\Event;
use App\Domain\Entities\Organizer;
use App\Domain\Entities\Promoter;
use App\Domain\Entities\Venue;
use App\Domain\Enums\DataExportRequestStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GenerateOrganizerDataExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $organizerId,
        private readonly int $dataExportRequestId,
    ) {}

    public function handle(
        OrganizerRepositoryInterface $organizers,
        VenueRepositoryInterface $venues,
        EventRepositoryInterface $events,
        PromoterRepositoryInterface $promoters,
        DataExportRequestRepositoryInterface $dataExportRequests,
    ): void {
        try {
            $organizer = $organizers->findById($this->organizerId) ?? throw new RuntimeException(
                "Organizer {$this->organizerId} not found."
            );

            $archive = [
                'organizer' => $this->organizerToArray($organizer),
                'venue' => ($venue = $venues->findByOrganizerId($this->organizerId)) ? $this->venueToArray($venue) : null,
                'events' => array_map($this->eventToArray(...), $events->findByOrganizerId($this->organizerId)),
                'promoters' => array_map($this->promoterToArray(...), $promoters->findByOrganizerId($this->organizerId)),
            ];

            $path = AdminPanelConstants::DATA_EXPORT_STORAGE_DIRECTORY.'/'.bin2hex(random_bytes(16)).'.json';

            if (Storage::disk('s3')->put($path, json_encode($archive, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)) === false) {
                throw new RuntimeException("Failed to write export archive to {$path}.");
            }

            $dataExportRequests->update($this->dataExportRequestId, [
                'status' => DataExportRequestStatus::Ready,
                'download_path' => $path,
            ]);
        } catch (Throwable $exception) {
            $dataExportRequests->update($this->dataExportRequestId, [
                'status' => DataExportRequestStatus::Failed,
            ]);

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function organizerToArray(Organizer $organizer): array
    {
        return [
            'id' => $organizer->id,
            'orgName' => $organizer->orgName,
            'contactName' => $organizer->contactName,
            'email' => $organizer->email,
            'phone' => $organizer->phone,
            'planTier' => $organizer->planTier->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function venueToArray(Venue $venue): array
    {
        return [
            'id' => $venue->id,
            'name' => $venue->name,
            'description' => $venue->description,
            'address' => $venue->address,
            'contactInfo' => $venue->contactInfo,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventToArray(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'dateTime' => $event->dateTime->format(DATE_ATOM),
            'status' => $event->status->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function promoterToArray(Promoter $promoter): array
    {
        return [
            'id' => $promoter->id,
            'name' => $promoter->name,
            'phone' => $promoter->phone,
            'email' => $promoter->email,
        ];
    }
}
