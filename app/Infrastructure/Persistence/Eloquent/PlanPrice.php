<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Enums\PlanTier;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['tier', 'amount', 'effective_from', 'effective_to', 'set_by_super_admin_id'])]
class PlanPrice extends Model
{
    protected function casts(): array
    {
        return [
            'tier' => PlanTier::class,
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
        ];
    }
}
