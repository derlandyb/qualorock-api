<?php

namespace App\Application\Policies;

use App\Domain\Entities\Promoter;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Contracts\Auth\Authenticatable;

class PromoterPolicy
{
    public function owns(?Authenticatable $user, Promoter $promoter): bool
    {
        return $user instanceof Organizer && $user->id === $promoter->organizerId;
    }
}
