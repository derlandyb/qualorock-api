<?php

namespace App\Presentation\Http\Controllers\Organizer;

use App\Application\UseCases\OrganizerData\ExportOrganizerData;
use App\Domain\Entities\DataExportRequest;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Organizer\RequestDataExportRequest;
use Illuminate\Http\JsonResponse;

class OrganizerDataController extends Controller
{
    public function __construct(
        private readonly ExportOrganizerData $exportOrganizerData,
    ) {}

    public function export(RequestDataExportRequest $request): JsonResponse
    {
        $dataExportRequest = $this->exportOrganizerData->handle((int) $request->user('organizer')->id);

        return response()->json(['data' => $this->toResponse($dataExportRequest)], 202);
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
