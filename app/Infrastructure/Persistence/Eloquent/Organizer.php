<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Enums\OrganizerApprovalState;
use App\Domain\Enums\PlanTier;
use Database\Factories\OrganizerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['org_name', 'contact_name', 'email', 'phone', 'password_hash', 'plan_tier', 'approval_state', 'rejection_reason', 'consent_given_at', 'personal_data_purged_at'])]
#[Hidden(['password_hash'])]
class Organizer extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected static function newFactory(): OrganizerFactory
    {
        return OrganizerFactory::new();
    }

    protected function casts(): array
    {
        return [
            'plan_tier' => PlanTier::class,
            'approval_state' => OrganizerApprovalState::class,
            'consent_given_at' => 'datetime',
            'deleted_at' => 'datetime',
            'personal_data_purged_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }
}
