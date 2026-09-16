<?php

namespace App\Domain\Constants;

final class AdminPanelConstants
{
    public const string ORGANIZER_APPROVAL_STATE_SESSION_KEY = 'organizer_approval_state';

    public const string ORGANIZER_REJECTION_REASON_SESSION_KEY = 'organizer_rejection_reason';

    public const int REJECTION_REASON_MAX_LENGTH = 1000;

    public const string DEFAULT_ORGANIZER_TIMEZONE = 'America/Sao_Paulo';

    public const int BASIC_TIER_MONTHLY_PUBLISH_LIMIT = 4;
}
