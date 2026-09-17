<?php

namespace App\Application\UseCases\EventInfoRequest;

use App\Domain\Contracts\EventInfoRequestRepositoryInterface;
use App\Domain\Entities\EventInfoRequest;

class RespondToEventInfoRequest
{
    public function __construct(
        private readonly EventInfoRequestRepositoryInterface $eventInfoRequests,
    ) {}

    public function handle(int $eventInfoRequestId, string $response): EventInfoRequest
    {
        return $this->eventInfoRequests->respond($eventInfoRequestId, $response);
    }
}
