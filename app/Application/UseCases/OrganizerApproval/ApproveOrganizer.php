<?php

namespace App\Application\UseCases\OrganizerApproval;

use App\Domain\Contracts\OrganizerRepositoryInterface;
use App\Domain\Entities\Organizer;
use App\Domain\Enums\OrganizerApprovalState;

class ApproveOrganizer
{
    public function __construct(
        private readonly OrganizerRepositoryInterface $organizers,
    ) {}

    public function handle(int $organizerId): Organizer
    {
        return $this->organizers->update($organizerId, [
            'approval_state' => OrganizerApprovalState::Approved,
            'rejection_reason' => null,
        ]);
    }
}
