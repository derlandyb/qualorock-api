<?php

namespace App\Presentation\Http\Controllers\Organizer;

use App\Application\UseCases\EventInfoRequest\ListEventInfoRequests;
use App\Application\UseCases\EventInfoRequest\RespondToEventInfoRequest;
use App\Domain\Entities\EventInfoRequest;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Organizer\ListEventInfoRequestsRequest;
use App\Presentation\Http\Requests\Organizer\RespondToEventInfoRequestRequest;
use Illuminate\Http\JsonResponse;

class EventInfoRequestController extends Controller
{
    public function __construct(
        private readonly ListEventInfoRequests $listEventInfoRequests,
        private readonly RespondToEventInfoRequest $respondToEventInfoRequest,
    ) {}

    public function index(ListEventInfoRequestsRequest $request): JsonResponse
    {
        $eventInfoRequests = $this->listEventInfoRequests->handle($request->event()->id);

        return response()->json(['data' => array_map($this->toResponse(...), $eventInfoRequests)]);
    }

    public function respond(RespondToEventInfoRequestRequest $request): JsonResponse
    {
        $eventInfoRequest = $this->respondToEventInfoRequest->handle(
            $request->eventInfoRequest()->id,
            $request->validated('response'),
        );

        return response()->json(['data' => $this->toResponse($eventInfoRequest)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResponse(EventInfoRequest $eventInfoRequest): array
    {
        return [
            'id' => $eventInfoRequest->id,
            'eventId' => $eventInfoRequest->eventId,
            'consumerUserId' => $eventInfoRequest->consumerUserId,
            'message' => $eventInfoRequest->message,
            'organizerResponse' => $eventInfoRequest->organizerResponse,
            'respondedAt' => $eventInfoRequest->respondedAt?->format(DATE_ATOM),
        ];
    }
}
