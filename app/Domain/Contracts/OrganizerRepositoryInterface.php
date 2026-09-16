<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\Organizer;
use App\Domain\Enums\OrganizerApprovalState;
use Illuminate\Support\Collection;

interface OrganizerRepositoryInterface
{
    public function findById(int $id): ?Organizer;

    public function findByEmail(string $email): ?Organizer;

    /**
     * @return Collection<int, Organizer>
     */
    public function findByApprovalState(OrganizerApprovalState $state): Collection;

    public function create(Organizer $organizer): Organizer;

    public function update(int $id, array $attributes): Organizer;
}
