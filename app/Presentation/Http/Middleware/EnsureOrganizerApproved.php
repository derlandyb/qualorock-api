<?php

namespace App\Presentation\Http\Middleware;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Enums\OrganizerApprovalState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Denies management access unless the organizer's approval state, as
 * snapshotted into the session at login, is `approved`. Deliberately reads
 * the session snapshot rather than the organizer's live database record:
 * an approval granted mid-session must not silently unlock an already
 * active session - the organizer has to log in again to pick it up.
 */
class EnsureOrganizerApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $state = $request->session()->get(AdminPanelConstants::ORGANIZER_APPROVAL_STATE_SESSION_KEY);

        if ($state !== OrganizerApprovalState::Approved->value) {
            return response()->json([
                'state' => $state,
                'rejectionReason' => $request->session()->get(AdminPanelConstants::ORGANIZER_REJECTION_REASON_SESSION_KEY),
            ], 403);
        }

        return $next($request);
    }
}
