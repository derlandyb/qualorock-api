<?php

namespace App\Domain\Entities;

final class Venue
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $organizerId,
        public readonly string $name,
        public readonly string $description,
        public readonly string $address,
        public readonly string $contactInfo,
        public readonly ?string $imageUrl,
    ) {}
}
