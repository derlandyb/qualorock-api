<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Enums\OrganizerApprovalState;
use App\Domain\Enums\PlanTier;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['org_name', 'contact_name', 'email', 'phone', 'password_hash', 'plan_tier', 'approval_state', 'rejection_reason', 'consent_given_at'])]
#[Hidden(['password_hash'])]
class Organizer extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'plan_tier' => PlanTier::class,
            'approval_state' => OrganizerApprovalState::class,
            'consent_given_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
