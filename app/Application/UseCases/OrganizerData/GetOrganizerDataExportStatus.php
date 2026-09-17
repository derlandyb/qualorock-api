<?php

namespace App\Application\UseCases\OrganizerData;

use App\Domain\Contracts\DataExportRequestRepositoryInterface;
use App\Domain\Entities\DataExportRequest;
use App\Domain\Exceptions\DataExportRequestNotFoundException;

class GetOrganizerDataExportStatus
{
    public function __construct(
        private readonly DataExportRequestRepositoryInterface $dataExportRequests,
    ) {}

    /**
     * @return array{dataExportRequest: DataExportRequest, downloadUrl: ?string}
     */
    public function handle(int $organizerId, int $exportRequestId): array
    {
        $dataExportRequest = $this->dataExportRequests->findById($exportRequestId);

        if ($dataExportRequest === null || $dataExportRequest->organizerId !== $organizerId) {
            throw new DataExportRequestNotFoundException;
        }

        return [
            'dataExportRequest' => $dataExportRequest,
            'downloadUrl' => $this->dataExportRequests->temporaryDownloadUrl($exportRequestId),
        ];
    }
}
