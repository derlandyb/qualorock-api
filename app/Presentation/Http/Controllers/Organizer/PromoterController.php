<?php

namespace App\Presentation\Http\Controllers\Organizer;

use App\Application\UseCases\Promoter\CreatePromoter;
use App\Application\UseCases\Promoter\DeletePromoter;
use App\Application\UseCases\Promoter\GetEventPromoters;
use App\Application\UseCases\Promoter\LinkPromoterToEvent;
use App\Application\UseCases\Promoter\UnlinkPromoterFromEvent;
use App\Application\UseCases\Promoter\UpdatePromoter;
use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Entities\Promoter;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Organizer\CreatePromoterRequest;
use App\Presentation\Http\Requests\Organizer\DeletePromoterRequest;
use App\Presentation\Http\Requests\Organizer\LinkPromoterRequest;
use App\Presentation\Http\Requests\Organizer\ShowEventPromotersRequest;
use App\Presentation\Http\Requests\Organizer\UnlinkPromoterRequest;
use App\Presentation\Http\Requests\Organizer\UpdatePromoterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoterController extends Controller
{
    public function __construct(
        private readonly PromoterRepositoryInterface $promoters,
        private readonly CreatePromoter $createPromoter,
        private readonly UpdatePromoter $updatePromoter,
        private readonly DeletePromoter $deletePromoter,
        private readonly LinkPromoterToEvent $linkPromoterToEvent,
        private readonly UnlinkPromoterFromEvent $unlinkPromoterFromEvent,
        private readonly GetEventPromoters $getEventPromoters,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $promoters = $this->promoters->findByOrganizerId((int) $request->user('organizer')->id);

        return response()->json(['data' => array_map($this->toResponse(...), $promoters)]);
    }

    public function store(CreatePromoterRequest $request): JsonResponse
    {
        $promoter = $this->createPromoter->handle((int) $request->user('organizer')->id, $request->validated());

        return response()->json(['data' => $this->toResponse($promoter)], 201);
    }

    public function update(UpdatePromoterRequest $request): JsonResponse
    {
        $promoter = $this->updatePromoter->handle($request->promoter()->id, $request->validated());

        return response()->json(['data' => $this->toResponse($promoter)]);
    }

    public function destroy(DeletePromoterRequest $request): JsonResponse
    {
        $this->deletePromoter->handle($request->promoter()->id);

        return response()->json(status: 204);
    }

    public function link(LinkPromoterRequest $request): JsonResponse
    {
        $this->linkPromoterToEvent->handle($request->promoter()->id, $request->event()->id);

        return response()->json(status: 204);
    }

    public function unlink(UnlinkPromoterRequest $request): JsonResponse
    {
        $this->unlinkPromoterFromEvent->handle($request->promoter()->id, $request->event()->id);

        return response()->json(status: 204);
    }

    public function eventPromoters(ShowEventPromotersRequest $request): JsonResponse
    {
        $promoters = $this->getEventPromoters->handle($request->event()->id);

        return response()->json(['data' => array_map($this->toResponse(...), $promoters)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResponse(Promoter $promoter): array
    {
        return [
            'id' => $promoter->id,
            'organizerId' => $promoter->organizerId,
            'name' => $promoter->name,
            'phone' => $promoter->phone,
            'email' => $promoter->email,
            'instagramUrl' => $promoter->instagramUrl,
            'tiktokUrl' => $promoter->tiktokUrl,
        ];
    }
}
