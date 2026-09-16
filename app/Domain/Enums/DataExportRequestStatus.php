<?php

namespace App\Domain\Enums;

enum DataExportRequestStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
    case Failed = 'failed';
}
