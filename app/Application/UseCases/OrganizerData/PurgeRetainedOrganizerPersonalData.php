<?php

namespace App\Application\UseCases\OrganizerData;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Contracts\DataExportRequestRepositoryInterface;
use App\Domain\Contracts\OrganizerRepositoryInterface;

class PurgeRetainedOrganizerPersonalData
{
    public function __construct(
        private readonly OrganizerRepositoryInterface $organizers,
        private readonly DataExportRequestRepositoryInterface $dataExportRequests,
    ) {}

    public function handle(): int
    {
        $organizers = $this->organizers->findPendingPersonalDataPurge(AdminPanelConstants::DELETION_RETENTION_DAYS);

        foreach ($organizers as $organizer) {
            foreach ($this->dataExportRequests->findByOrganizerId($organizer->id) as $dataExportRequest) {
                $this->dataExportRequests->deleteArchive($dataExportRequest->id);
            }

            $this->organizers->purgePersonalData($organizer->id);
        }

        return count($organizers);
    }
}
