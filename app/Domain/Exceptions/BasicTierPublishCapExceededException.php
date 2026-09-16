<?php

namespace App\Domain\Exceptions;

use RuntimeException;

class BasicTierPublishCapExceededException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Basic-tier organizers cannot publish more events this calendar month.');
    }
}
