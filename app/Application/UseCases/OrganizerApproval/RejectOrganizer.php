<?php

namespace App\Application\UseCases\OrganizerApproval;

use App\Domain\Contracts\OrganizerRepositoryInterface;
use App\Domain\Entities\Organizer;
use App\Domain\Enums\OrganizerApprovalState;

class RejectOrganizer
{
    public function __construct(
        private readonly OrganizerRepositoryInterface $organizers,
    ) {}

    public function handle(int $organizerId, ?string $reason): Organizer
    {
        return $this->organizers->update($organizerId, [
            'approval_state' => OrganizerApprovalState::Rejected,
            'rejection_reason' => $reason,
        ]);
    }
}
