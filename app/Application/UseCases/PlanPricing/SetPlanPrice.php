<?php

namespace App\Application\UseCases\PlanPricing;

use App\Domain\Contracts\PlanPriceRepositoryInterface;
use App\Domain\Entities\PlanPrice;
use App\Domain\Enums\PlanTier;
use Illuminate\Support\Carbon;

class SetPlanPrice
{
    public function __construct(
        private readonly PlanPriceRepositoryInterface $planPrices,
    ) {}

    public function handle(int $amount, int $superAdminId, PlanTier $tier = PlanTier::Plus): PlanPrice
    {
        $planPrice = new PlanPrice(
            id: null,
            tier: $tier,
            amount: $amount,
            effectiveFrom: Carbon::now()->toImmutable(),
            effectiveTo: null,
            setBySuperAdminId: $superAdminId,
        );

        return $this->planPrices->setNewCurrent($planPrice);
    }
}
