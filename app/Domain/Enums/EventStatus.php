<?php

namespace App\Domain\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Cancelled = 'cancelled';
    case Closed = 'closed';
}
