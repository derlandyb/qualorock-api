<?php

namespace App\Domain\Entities;

use App\Domain\Enums\PlanTier;
use DateTimeImmutable;

final class PlanPrice
{
    public function __construct(
        public readonly ?int $id,
        public readonly PlanTier $tier,
        public readonly int $amount,
        public readonly DateTimeImmutable $effectiveFrom,
        public readonly ?DateTimeImmutable $effectiveTo,
        public readonly int $setBySuperAdminId,
    ) {}
}
