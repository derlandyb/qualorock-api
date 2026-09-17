<?php

namespace App\Application\Policies;

use App\Infrastructure\Persistence\Eloquent\SuperAdmin;
use Illuminate\Contracts\Auth\Authenticatable;

class SuperAdminOrganizerPolicy
{
    public function viewPending(?Authenticatable $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function approve(?Authenticatable $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function reject(?Authenticatable $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function delete(?Authenticatable $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    private function isSuperAdmin(?Authenticatable $user): bool
    {
        return $user instanceof SuperAdmin;
    }
}
