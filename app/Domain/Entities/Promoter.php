<?php

namespace App\Domain\Entities;

final class Promoter
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $organizerId,
        public readonly string $name,
        public readonly string $phone,
        public readonly string $email,
        public readonly string $instagramUrl,
        public readonly string $tiktokUrl,
    ) {}
}
