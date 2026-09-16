<?php

namespace App\Application\Policies;

use App\Infrastructure\Persistence\Eloquent\SuperAdmin;
use Illuminate\Contracts\Auth\Authenticatable;

class SuperAdminOrganizerPolicy
{
    public function viewPending(?Authenticatable $user): bool
    {
        return $user instanceof SuperAdmin;
    }

    public function approve(?Authenticatable $user): bool
    {
        return $user instanceof SuperAdmin;
    }

    public function reject(?Authenticatable $user): bool
    {
        return $user instanceof SuperAdmin;
    }
}
