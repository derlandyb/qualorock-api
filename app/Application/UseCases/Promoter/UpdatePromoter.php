<?php

namespace App\Application\UseCases\Promoter;

use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Entities\Promoter;

class UpdatePromoter
{
    /**
     * Maps validated camelCase request fields to their snake_case columns -
     * only fields present here can ever reach the repository's update(),
     * so a caller can never forward arbitrary/unvalidated attributes.
     */
    private const array FIELD_MAP = [
        'name' => 'name',
        'phone' => 'phone',
        'email' => 'email',
        'instagramUrl' => 'instagram_url',
        'tiktokUrl' => 'tiktok_url',
    ];

    public function __construct(
        private readonly PromoterRepositoryInterface $promoters,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(int $promoterId, array $data): Promoter
    {
        $attributes = [];

        foreach (self::FIELD_MAP as $camelCase => $column) {
            if (array_key_exists($camelCase, $data)) {
                $attributes[$column] = $data[$camelCase];
            }
        }

        return $this->promoters->update($promoterId, $attributes);
    }
}
