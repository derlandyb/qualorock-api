<?php

namespace App\Application\UseCases\PlanPricing;

use App\Domain\Contracts\PlanPriceRepositoryInterface;
use App\Domain\Entities\PlanPrice;
use App\Domain\Enums\PlanTier;

class GetPlanPriceHistory
{
    public function __construct(
        private readonly PlanPriceRepositoryInterface $planPrices,
    ) {}

    /**
     * @return array<int, PlanPrice>
     */
    public function handle(PlanTier $tier = PlanTier::Plus): array
    {
        return $this->planPrices->history($tier);
    }
}
