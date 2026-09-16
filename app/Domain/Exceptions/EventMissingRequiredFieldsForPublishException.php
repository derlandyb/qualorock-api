<?php

namespace App\Domain\Exceptions;

use RuntimeException;

class EventMissingRequiredFieldsForPublishException extends RuntimeException
{
    /**
     * @param  array<int, string>  $missingFields
     */
    public function __construct(
        public readonly array $missingFields,
    ) {
        parent::__construct('Cannot publish an event with missing required fields.');
    }
}
