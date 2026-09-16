<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contracts\PlanPriceRepositoryInterface;
use App\Domain\Entities\PlanPrice as PlanPriceEntity;
use App\Domain\Enums\PlanTier;

class EloquentPlanPriceRepository implements PlanPriceRepositoryInterface
{
    public function findCurrent(PlanTier $tier): ?PlanPriceEntity
    {
        $model = PlanPrice::where('tier', $tier->value)
            ->whereNull('effective_to')
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function create(PlanPriceEntity $planPrice): PlanPriceEntity
    {
        $model = PlanPrice::create([
            'tier' => $planPrice->tier,
            'amount' => $planPrice->amount,
            'effective_from' => $planPrice->effectiveFrom,
            'effective_to' => $planPrice->effectiveTo,
            'set_by_super_admin_id' => $planPrice->setBySuperAdminId,
        ]);

        return $this->toEntity($model);
    }

    private function toEntity(PlanPrice $model): PlanPriceEntity
    {
        return new PlanPriceEntity(
            id: $model->id,
            tier: $model->tier,
            amount: $model->amount,
            effectiveFrom: $model->effective_from->toImmutable(),
            effectiveTo: $model->effective_to?->toImmutable(),
            setBySuperAdminId: $model->set_by_super_admin_id,
        );
    }
}
