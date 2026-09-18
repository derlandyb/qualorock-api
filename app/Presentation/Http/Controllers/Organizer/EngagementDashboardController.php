<?php

namespace App\Presentation\Http\Controllers\Organizer;

use App\Application\UseCases\Engagement\GetEventEngagement;
use App\Application\UseCases\Engagement\GetOrganizerEngagementSummary;
use App\Domain\Entities\EventEngagement;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Organizer\ShowEventEngagementRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EngagementDashboardController extends Controller
{
    public function __construct(
        private readonly GetEventEngagement $getEventEngagement,
        private readonly GetOrganizerEngagementSummary $getOrganizerEngagementSummary,
    ) {}

    public function show(ShowEventEngagementRequest $request): JsonResponse
    {
        $engagement = $this->getEventEngagement->handle($request->event()->id);

        return response()->json(['data' => $this->toResponse($engagement)]);
    }

    public function summary(Request $request): JsonResponse
    {
        $summary = $this->getOrganizerEngagementSummary->handle((int) $request->user('organizer')->id);

        return response()->json(['data' => array_map($this->toResponse(...), $summary)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResponse(EventEngagement $engagement): array
    {
        return [
            'eventId' => $engagement->eventId,
            'viewsCount' => $engagement->viewsCount,
            'favoritesCount' => $engagement->favoritesCount,
            'ticketLinkClicksCount' => $engagement->ticketLinkClicksCount,
            'interestCount' => $engagement->interestCount,
        ];
    }
}
