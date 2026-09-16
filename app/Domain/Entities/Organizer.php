<?php

namespace App\Domain\Entities;

use App\Domain\Enums\OrganizerApprovalState;
use App\Domain\Enums\PlanTier;
use DateTimeImmutable;

final class Organizer
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $orgName,
        public readonly string $contactName,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $passwordHash,
        public readonly PlanTier $planTier,
        public readonly OrganizerApprovalState $approvalState,
        public readonly ?string $rejectionReason,
        public readonly DateTimeImmutable $consentGivenAt,
    ) {
    }
}
