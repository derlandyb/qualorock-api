<?php

namespace App\Application\UseCases\OrganizerData;

use App\Domain\Contracts\DataExportRequestRepositoryInterface;
use App\Domain\Entities\DataExportRequest;
use App\Domain\Enums\DataExportRequestStatus;
use App\Infrastructure\Jobs\GenerateOrganizerDataExportJob;
use Illuminate\Support\Carbon;

class ExportOrganizerData
{
    public function __construct(
        private readonly DataExportRequestRepositoryInterface $dataExportRequests,
    ) {}

    public function handle(int $organizerId): DataExportRequest
    {
        $dataExportRequest = $this->dataExportRequests->create(new DataExportRequest(
            id: null,
            organizerId: $organizerId,
            status: DataExportRequestStatus::Pending,
            downloadUrl: null,
            requestedAt: Carbon::now()->toImmutable(),
        ));

        GenerateOrganizerDataExportJob::dispatch($organizerId, $dataExportRequest->id);

        return $dataExportRequest;
    }
}
