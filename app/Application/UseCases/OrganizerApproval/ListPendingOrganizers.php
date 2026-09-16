<?php

namespace App\Application\UseCases\OrganizerApproval;

use App\Domain\Contracts\OrganizerRepositoryInterface;
use App\Domain\Entities\Organizer;
use App\Domain\Enums\OrganizerApprovalState;

class ListPendingOrganizers
{
    public function __construct(
        private readonly OrganizerRepositoryInterface $organizers,
    ) {}

    /**
     * @return array<int, Organizer>
     */
    public function handle(): array
    {
        return $this->organizers->findByApprovalState(OrganizerApprovalState::Pending);
    }
}
