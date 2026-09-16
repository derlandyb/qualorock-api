<?php

namespace App\Application\UseCases\Promoter;

use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Entities\Promoter;

class GetEventPromoters
{
    public function __construct(
        private readonly PromoterRepositoryInterface $promoters,
    ) {}

    /**
     * @return array<int, Promoter>
     */
    public function handle(int $eventId): array
    {
        return $this->promoters->findByEventId($eventId);
    }
}
