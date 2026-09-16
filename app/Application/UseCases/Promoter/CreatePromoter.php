<?php

namespace App\Application\UseCases\Promoter;

use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Entities\Promoter;

class CreatePromoter
{
    public function __construct(
        private readonly PromoterRepositoryInterface $promoters,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(int $organizerId, array $data): Promoter
    {
        return $this->promoters->create(new Promoter(
            id: null,
            organizerId: $organizerId,
            name: $data['name'],
            phone: $data['phone'],
            email: $data['email'],
            instagramUrl: $data['instagramUrl'],
            tiktokUrl: $data['tiktokUrl'],
        ));
    }
}
