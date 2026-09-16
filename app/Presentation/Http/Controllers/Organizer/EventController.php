<?php

namespace App\Presentation\Http\Controllers\Organizer;

use App\Application\UseCases\Event\CreateEvent;
use App\Application\UseCases\Event\DeleteEvent;
use App\Application\UseCases\Event\DuplicateEvent;
use App\Application\UseCases\Event\TransitionEventStatus;
use App\Application\UseCases\Event\UpdateEvent;
use App\Domain\Entities\Event;
use App\Domain\Exceptions\BasicTierPublishCapExceededException;
use App\Domain\Exceptions\EventMissingRequiredFieldsForPublishException;
use App\Domain\Exceptions\InvalidEventStatusTransitionException;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Organizer\CreateEventRequest;
use App\Presentation\Http\Requests\Organizer\DeleteEventRequest;
use App\Presentation\Http\Requests\Organizer\DuplicateEventRequest;
use App\Presentation\Http\Requests\Organizer\TransitionEventStatusRequest;
use App\Presentation\Http\Requests\Organizer\UpdateEventRequest;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        private readonly CreateEvent $createEvent,
        private readonly UpdateEvent $updateEvent,
        private readonly DeleteEvent $deleteEvent,
        private readonly DuplicateEvent $duplicateEvent,
        private readonly TransitionEventStatus $transitionEventStatus,
    ) {}

    public function store(CreateEventRequest $request): JsonResponse
    {
        $event = $this->createEvent->handle((int) $request->user('organizer')->id, $request->validated());

        return response()->json(['data' => $this->toResponse($event)], 201);
    }

    public function update(UpdateEventRequest $request): JsonResponse
    {
        $event = $this->updateEvent->handle($request->event()->id, $request->validated());

        return response()->json(['data' => $this->toResponse($event)]);
    }

    public function destroy(DeleteEventRequest $request): JsonResponse
    {
        $this->deleteEvent->handle($request->event()->id);

        return response()->json(status: 204);
    }

    public function duplicate(DuplicateEventRequest $request): JsonResponse
    {
        $duplicate = $this->duplicateEvent->handle($request->event());

        return response()->json(['data' => $this->toResponse($duplicate)], 201);
    }

    public function transitionStatus(TransitionEventStatusRequest $request): JsonResponse
    {
        try {
            $event = $this->transitionEventStatus->handle(
                $request->event(),
                $request->targetStatus(),
                $request->user('organizer')->plan_tier,
            );
        } catch (InvalidEventStatusTransitionException) {
            return response()->json(['error' => 'invalid_transition'], 422);
        } catch (EventMissingRequiredFieldsForPublishException $exception) {
            return response()->json([
                'error' => 'missing_required_fields',
                'missingFields' => $exception->missingFields,
            ], 422);
        } catch (BasicTierPublishCapExceededException) {
            return response()->json(['error' => 'upgrade_required'], 422);
        }

        return response()->json(['data' => $this->toResponse($event)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResponse(Event $event): array
    {
        return [
            'id' => $event->id,
            'organizerId' => $event->organizerId,
            'venueId' => $event->venueId,
            'title' => $event->title,
            'description' => $event->description,
            'dateTime' => $event->dateTime->format(DATE_ATOM),
            'location' => $event->location,
            'fullAddress' => $event->fullAddress,
            'featuredImageUrl' => $event->featuredImageUrl,
            'externalTicketLink' => $event->externalTicketLink,
            'priceType' => $event->priceType->value,
            'musicCategory' => $event->musicCategory,
            'capacity' => $event->capacity,
            'ageRange' => $event->ageRange,
            'additionalInfo' => $event->additionalInfo,
            'accessibilityInfo' => $event->accessibilityInfo,
            'eventRules' => $event->eventRules,
            'status' => $event->status->value,
            'publishedAt' => $event->publishedAt?->format(DATE_ATOM),
        ];
    }
}
