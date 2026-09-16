<?php

namespace App\Presentation\Http\Controllers\Organizer;

use App\Application\UseCases\Venue\GetVenueAgenda;
use App\Application\UseCases\Venue\GetVenueHistory;
use App\Application\UseCases\Venue\UpdateVenue;
use App\Domain\Entities\Event;
use App\Domain\Entities\Venue;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Organizer\ShowVenueRequest;
use App\Presentation\Http\Requests\Organizer\UpdateVenueRequest;
use Illuminate\Http\JsonResponse;

class VenueController extends Controller
{
    public function __construct(
        private readonly UpdateVenue $updateVenue,
        private readonly GetVenueAgenda $getVenueAgenda,
        private readonly GetVenueHistory $getVenueHistory,
    ) {}

    public function show(ShowVenueRequest $request): JsonResponse
    {
        $venue = $request->venue();

        if ($venue === null) {
            return response()->json(status: 404);
        }

        return response()->json(['data' => $this->toResponse($venue)]);
    }

    public function update(UpdateVenueRequest $request): JsonResponse
    {
        $venue = $this->updateVenue->handle($request->venue()->id, $request->validated());

        return response()->json(['data' => $this->toResponse($venue)]);
    }

    public function agenda(ShowVenueRequest $request): JsonResponse
    {
        $venue = $request->venue();

        if ($venue === null) {
            return response()->json(status: 404);
        }

        $events = $this->getVenueAgenda->handle($venue->id);

        return response()->json(['data' => array_map($this->toEventResponse(...), $events)]);
    }

    public function history(ShowVenueRequest $request): JsonResponse
    {
        $venue = $request->venue();

        if ($venue === null) {
            return response()->json(status: 404);
        }

        $events = $this->getVenueHistory->handle($venue->id);

        return response()->json(['data' => array_map($this->toEventResponse(...), $events)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResponse(Venue $venue): array
    {
        return [
            'id' => $venue->id,
            'organizerId' => $venue->organizerId,
            'name' => $venue->name,
            'description' => $venue->description,
            'address' => $venue->address,
            'contactInfo' => $venue->contactInfo,
            'imageUrl' => $venue->imageUrl,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toEventResponse(Event $event): array
    {
        return [
            'id' => $event->id,
            'venueId' => $event->venueId,
            'title' => $event->title,
            'dateTime' => $event->dateTime->format(DATE_ATOM),
            'status' => $event->status->value,
        ];
    }
}
