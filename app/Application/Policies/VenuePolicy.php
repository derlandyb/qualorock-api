<?php

namespace App\Application\Policies;

use App\Domain\Entities\Venue;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Contracts\Auth\Authenticatable;

class VenuePolicy
{
    public function owns(?Authenticatable $user, Venue $venue): bool
    {
        return $user instanceof Organizer && $user->id === $venue->organizerId;
    }
}
