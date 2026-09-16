<?php

namespace App\Domain\Exceptions;

use App\Domain\Enums\EventStatus;
use RuntimeException;

class InvalidEventStatusTransitionException extends RuntimeException
{
    public function __construct(
        public readonly EventStatus $from,
        public readonly EventStatus $to,
    ) {
        parent::__construct("Cannot transition event from {$from->value} to {$to->value}.");
    }
}
