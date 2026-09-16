<?php

namespace Database\Factories;

use App\Domain\Enums\OrganizerApprovalState;
use App\Domain\Enums\PlanTier;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Organizer>
 */
class OrganizerFactory extends Factory
{
    protected $model = Organizer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'org_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'password_hash' => Hash::make('password'),
            'plan_tier' => PlanTier::Basic,
            'approval_state' => OrganizerApprovalState::Pending,
            'rejection_reason' => null,
            'consent_given_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_state' => OrganizerApprovalState::Pending,
            'rejection_reason' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_state' => OrganizerApprovalState::Approved,
            'rejection_reason' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_state' => OrganizerApprovalState::Rejected,
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
