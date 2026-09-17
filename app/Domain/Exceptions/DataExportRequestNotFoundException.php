<?php

namespace App\Domain\Exceptions;

use RuntimeException;

class DataExportRequestNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No matching data export request was found for this organizer.');
    }
}
