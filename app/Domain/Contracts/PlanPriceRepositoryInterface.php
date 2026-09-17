<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\PlanPrice;
use App\Domain\Enums\PlanTier;

interface PlanPriceRepositoryInterface
{
    public function findCurrent(PlanTier $tier): ?PlanPrice;

    public function create(PlanPrice $planPrice): PlanPrice;

    /**
     * @return array<int, PlanPrice>
     */
    public function history(PlanTier $tier): array;

    public function setNewCurrent(PlanPrice $planPrice): PlanPrice;
}
