<?php

namespace App\Application\Policies;

use App\Domain\Entities\Event;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Contracts\Auth\Authenticatable;

class EventPolicy
{
    public function owns(?Authenticatable $user, Event $event): bool
    {
        return $user instanceof Organizer && $user->id === $event->organizerId;
    }
}
