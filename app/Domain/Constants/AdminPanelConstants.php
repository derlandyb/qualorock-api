<?php

namespace App\Domain\Constants;

final class AdminPanelConstants
{
    public const string ORGANIZER_APPROVAL_STATE_SESSION_KEY = 'organizer_approval_state';

    public const string ORGANIZER_REJECTION_REASON_SESSION_KEY = 'organizer_rejection_reason';

    public const int REJECTION_REASON_MAX_LENGTH = 1000;
}
