<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\PlanPrice;
use App\Domain\Enums\PlanTier;

interface PlanPriceRepositoryInterface
{
    public function findCurrent(PlanTier $tier): ?PlanPrice;

    public function create(PlanPrice $planPrice): PlanPrice;
}
