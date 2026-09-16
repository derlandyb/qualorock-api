<?php

namespace App\Application\UseCases\Promoter;

use App\Domain\Contracts\PromoterRepositoryInterface;

class UnlinkPromoterFromEvent
{
    public function __construct(
        private readonly PromoterRepositoryInterface $promoters,
    ) {}

    public function handle(int $promoterId, int $eventId): void
    {
        $this->promoters->unlinkFromEvent($promoterId, $eventId);
    }
}
