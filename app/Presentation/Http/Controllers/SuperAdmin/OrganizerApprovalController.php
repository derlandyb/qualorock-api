<?php

namespace App\Presentation\Http\Controllers\SuperAdmin;

use App\Application\Policies\SuperAdminOrganizerPolicy;
use App\Application\UseCases\OrganizerApproval\ApproveOrganizer;
use App\Application\UseCases\OrganizerApproval\ListPendingOrganizers;
use App\Application\UseCases\OrganizerApproval\RejectOrganizer;
use App\Domain\Entities\Organizer;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\SuperAdmin\RejectOrganizerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizerApprovalController extends Controller
{
    public function __construct(
        private readonly SuperAdminOrganizerPolicy $policy,
        private readonly ListPendingOrganizers $listPendingOrganizers,
        private readonly ApproveOrganizer $approveOrganizer,
        private readonly RejectOrganizer $rejectOrganizer,
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($this->policy->viewPending($request->user('super_admin')), 403);

        $organizers = $this->listPendingOrganizers->handle();

        return response()->json([
            'data' => array_map($this->toResponse(...), $organizers),
        ]);
    }

    public function approve(Request $request, int $organizer): JsonResponse
    {
        abort_unless($this->policy->approve($request->user('super_admin')), 403);

        return response()->json([
            'data' => $this->toResponse($this->approveOrganizer->handle($organizer)),
        ]);
    }

    public function reject(RejectOrganizerRequest $request, int $organizer): JsonResponse
    {
        return response()->json([
            'data' => $this->toResponse($this->rejectOrganizer->handle($organizer, $request->validated('reason'))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResponse(Organizer $organizer): array
    {
        return [
            'id' => $organizer->id,
            'orgName' => $organizer->orgName,
            'contactName' => $organizer->contactName,
            'email' => $organizer->email,
            'phone' => $organizer->phone,
            'planTier' => $organizer->planTier->value,
            'approvalState' => $organizer->approvalState->value,
            'rejectionReason' => $organizer->rejectionReason,
        ];
    }
}
