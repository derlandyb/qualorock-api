<?php

namespace App\Application\UseCases\OrganizerData;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Contracts\OrganizerRepositoryInterface;

class PurgeRetainedOrganizerPersonalData
{
    public function __construct(
        private readonly OrganizerRepositoryInterface $organizers,
    ) {}

    public function handle(): int
    {
        $organizers = $this->organizers->findPendingPersonalDataPurge(AdminPanelConstants::DELETION_RETENTION_DAYS);

        foreach ($organizers as $organizer) {
            $this->organizers->purgePersonalData($organizer->id);
        }

        return count($organizers);
    }
}
