<?php

namespace App\Domain\Exceptions;

use RuntimeException;

class OrganizerHasUpcomingPublishedEventException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This organizer has an upcoming published event; confirmation is required to delete the account.');
    }
}
