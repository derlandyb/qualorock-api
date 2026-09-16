<?php

namespace App\Application\UseCases\Promoter;

use App\Domain\Contracts\PromoterRepositoryInterface;

class LinkPromoterToEvent
{
    public function __construct(
        private readonly PromoterRepositoryInterface $promoters,
    ) {}

    public function handle(int $promoterId, int $eventId): void
    {
        $this->promoters->linkToEvent($promoterId, $eventId);
    }
}
