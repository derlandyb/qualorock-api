<?php

namespace Database\Factories;

use App\Domain\Enums\PlanTier;
use App\Infrastructure\Persistence\Eloquent\PlanPrice;
use App\Infrastructure\Persistence\Eloquent\SuperAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanPrice>
 */
class PlanPriceFactory extends Factory
{
    protected $model = PlanPrice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tier' => PlanTier::Plus,
            'amount' => fake()->numberBetween(1000, 10000),
            'effective_from' => now()->subMonth(),
            'effective_to' => null,
            'set_by_super_admin_id' => SuperAdmin::factory(),
        ];
    }
}
