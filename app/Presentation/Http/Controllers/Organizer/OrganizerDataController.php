<?php

namespace App\Presentation\Http\Controllers\Organizer;

use App\Application\UseCases\OrganizerData\DeleteOrganizerAccount;
use App\Application\UseCases\OrganizerData\ExportOrganizerData;
use App\Domain\Entities\DataExportRequest;
use App\Domain\Exceptions\OrganizerHasUpcomingPublishedEventException;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Organizer\DeleteOwnAccountRequest;
use App\Presentation\Http\Requests\Organizer\RequestDataExportRequest;
use App\Presentation\Http\Requests\SuperAdmin\SuperAdminDeleteOrganizerRequest;
use Illuminate\Http\JsonResponse;

class OrganizerDataController extends Controller
{
    public function __construct(
        private readonly ExportOrganizerData $exportOrganizerData,
        private readonly DeleteOrganizerAccount $deleteOrganizerAccount,
    ) {}

    public function export(RequestDataExportRequest $request): JsonResponse
    {
        $dataExportRequest = $this->exportOrganizerData->handle((int) $request->user('organizer')->id);

        return response()->json(['data' => $this->toResponse($dataExportRequest)], 202);
    }

    public function delete(DeleteOwnAccountRequest $request): JsonResponse
    {
        return $this->performDeletion((int) $request->user('organizer')->id, $request->confirmed());
    }

    public function superAdminDelete(SuperAdminDeleteOrganizerRequest $request, int $organizer): JsonResponse
    {
        return $this->performDeletion($organizer, $request->confirmed());
    }

    private function performDeletion(int $organizerId, bool $confirm): JsonResponse
    {
        try {
            $this->deleteOrganizerAccount->handle($organizerId, $confirm);
        } catch (OrganizerHasUpcomingPublishedEventException) {
            return response()->json(['error' => 'confirmation_required'], 409);
        }

        return response()->json(status: 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResponse(DataExportRequest $dataExportRequest): array
    {
        return [
            'id' => $dataExportRequest->id,
            'status' => $dataExportRequest->status->value,
            'downloadUrl' => $dataExportRequest->downloadUrl,
            'requestedAt' => $dataExportRequest->requestedAt->format(DATE_ATOM),
        ];
    }
}
