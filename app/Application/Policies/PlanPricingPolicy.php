<?php

namespace App\Application\Policies;

use App\Infrastructure\Persistence\Eloquent\SuperAdmin;
use Illuminate\Contracts\Auth\Authenticatable;

class PlanPricingPolicy
{
    public function view(?Authenticatable $user): bool
    {
        return $user instanceof SuperAdmin;
    }

    public function set(?Authenticatable $user): bool
    {
        return $user instanceof SuperAdmin;
    }
}
