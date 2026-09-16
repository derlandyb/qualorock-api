<?php

namespace App\Application\UseCases\OrganizerAuth;

use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Support\Facades\Auth;

class LoginOrganizer
{
    public function handle(string $email, string $password): ?Organizer
    {
        if (! Auth::guard('organizer')->attempt(['email' => $email, 'password' => $password])) {
            return null;
        }

        /** @var Organizer $organizer */
        $organizer = Auth::guard('organizer')->user();

        return $organizer;
    }
}
