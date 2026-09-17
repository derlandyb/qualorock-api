<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\Organizer;
use App\Domain\Enums\OrganizerApprovalState;

interface OrganizerRepositoryInterface
{
    public function findById(int $id): ?Organizer;

    public function findByEmail(string $email): ?Organizer;

    /**
     * @return array<int, Organizer>
     */
    public function findByApprovalState(OrganizerApprovalState $state): array;

    public function create(Organizer $organizer): Organizer;

    public function update(int $id, array $attributes): Organizer;

    public function softDelete(int $id): void;

    /**
     * @return array<int, Organizer>
     */
    public function findPendingPersonalDataPurge(int $retentionDays): array;

    public function purgePersonalData(int $id): Organizer;
}
