<?php

namespace App\Presentation\Http\Controllers\Organizer;

use App\Application\UseCases\OrganizerAuth\LoginOrganizer;
use App\Domain\Constants\AdminPanelConstants;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Organizer\LoginOrganizerRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginOrganizer $loginOrganizer,
    ) {}

    public function login(LoginOrganizerRequest $request): JsonResponse
    {
        $organizer = $this->loginOrganizer->handle(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        if ($organizer === null) {
            return response()->json(['message' => 'These credentials do not match our records.'], 401);
        }

        $request->session()->regenerate();
        $request->session()->put(AdminPanelConstants::ORGANIZER_APPROVAL_STATE_SESSION_KEY, $organizer->approval_state->value);
        $request->session()->put(AdminPanelConstants::ORGANIZER_REJECTION_REASON_SESSION_KEY, $organizer->rejection_reason);

        return response()->json([
            'data' => [
                'approvalState' => $organizer->approval_state->value,
                'rejectionReason' => $organizer->rejection_reason,
            ],
        ]);
    }
}
