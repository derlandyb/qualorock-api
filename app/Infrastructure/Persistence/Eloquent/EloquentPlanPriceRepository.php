<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contracts\PlanPriceRepositoryInterface;
use App\Domain\Entities\PlanPrice as PlanPriceEntity;
use App\Domain\Enums\PlanTier;
use Illuminate\Support\Facades\DB;

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
            'tier' => $planPrice->tier->value,
            'amount' => $planPrice->amount,
            'effective_from' => $planPrice->effectiveFrom,
            'effective_to' => $planPrice->effectiveTo,
            'set_by_super_admin_id' => $planPrice->setBySuperAdminId,
        ]);

        return $this->toEntity($model);
    }

    /**
     * @return array<int, PlanPriceEntity>
     */
    public function history(PlanTier $tier): array
    {
        return PlanPrice::where('tier', $tier->value)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get()
            ->map($this->toEntity(...))
            ->all();
    }

    public function setNewCurrent(PlanPriceEntity $planPrice): PlanPriceEntity
    {
        return DB::transaction(function () use ($planPrice): PlanPriceEntity {
            PlanPrice::where('tier', $planPrice->tier->value)
                ->whereNull('effective_to')
                ->lockForUpdate()
                ->update(['effective_to' => $planPrice->effectiveFrom]);

            return $this->create($planPrice);
        });
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
