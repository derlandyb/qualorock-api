<?php

namespace App\Domain\Enums;

enum OrganizerApprovalState: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
