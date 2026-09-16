<?php

namespace App\Application\UseCases\Promoter;

use App\Domain\Contracts\PromoterRepositoryInterface;

class DeletePromoter
{
    public function __construct(
        private readonly PromoterRepositoryInterface $promoters,
    ) {}

    public function handle(int $promoterId): void
    {
        $this->promoters->delete($promoterId);
    }
}
