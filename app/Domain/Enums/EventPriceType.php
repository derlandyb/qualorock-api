<?php

namespace App\Domain\Enums;

enum EventPriceType: string
{
    case Free = 'free';
    case Paid = 'paid';
}
