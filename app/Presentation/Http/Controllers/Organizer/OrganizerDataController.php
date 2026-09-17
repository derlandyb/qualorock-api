<?php

namespace App\Presentation\Http\Controllers\Organizer;

use App\Application\UseCases\OrganizerData\DeleteOrganizerAccount;
use App\Application\UseCases\OrganizerData\ExportOrganizerData;
use App\Application\UseCases\OrganizerData\GetOrganizerDataExportStatus;
use App\Domain\Entities\DataExportRequest;
use App\Domain\Exceptions\DataExportRequestNotFoundException;
use App\Domain\Exceptions\OrganizerHasUpcomingPublishedEventException;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Organizer\DeleteOwnAccountRequest;
use App\Presentation\Http\Requests\Organizer\RequestDataExportRequest;
use App\Presentation\Http\Requests\Organizer\ShowDataExportRequest;
use App\Presentation\Http\Requests\SuperAdmin\SuperAdminDeleteOrganizerRequest;
use Illuminate\Http\JsonResponse;

class OrganizerDataController extends Controller
{
    public function __construct(
        private readonly ExportOrganizerData $exportOrganizerData,
        private readonly GetOrganizerDataExportStatus $getOrganizerDataExportStatus,
        private readonly DeleteOrganizerAccount $deleteOrganizerAccount,
    ) {}

    public function export(RequestDataExportRequest $request): JsonResponse
    {
        $dataExportRequest = $this->exportOrganizerData->handle((int) $request->user('organizer')->id);

        return response()->json(['data' => $this->toResponse($dataExportRequest, null)], 202);
    }

    public function showExport(ShowDataExportRequest $request, int $dataExport): JsonResponse
    {
        try {
            $result = $this->getOrganizerDataExportStatus->handle((int) $request->user('organizer')->id, $dataExport);
        } catch (DataExportRequestNotFoundException) {
            return response()->json(['error' => 'not_found'], 404);
        }

        return response()->json(['data' => $this->toResponse($result['dataExportRequest'], $result['downloadUrl'])]);
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
    private function toResponse(DataExportRequest $dataExportRequest, ?string $downloadUrl): array
    {
        return [
            'id' => $dataExportRequest->id,
            'status' => $dataExportRequest->status->value,
            'downloadUrl' => $downloadUrl,
            'requestedAt' => $dataExportRequest->requestedAt->format(DATE_ATOM),
        ];
    }
}
