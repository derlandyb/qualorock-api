<?php

namespace App\Presentation\Http\Controllers\SuperAdmin;

use App\Application\Policies\PlanPricingPolicy;
use App\Application\UseCases\PlanPricing\GetPlanPriceHistory;
use App\Application\UseCases\PlanPricing\SetPlanPrice;
use App\Domain\Entities\PlanPrice;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\SuperAdmin\SetPlanPriceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanPricingController extends Controller
{
    public function __construct(
        private readonly PlanPricingPolicy $policy,
        private readonly GetPlanPriceHistory $getPlanPriceHistory,
        private readonly SetPlanPrice $setPlanPrice,
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($this->policy->view($request->user('super_admin')), 403);

        return response()->json([
            'data' => array_map($this->toResponse(...), $this->getPlanPriceHistory->handle()),
        ]);
    }

    public function store(SetPlanPriceRequest $request): JsonResponse
    {
        $planPrice = $this->setPlanPrice->handle(
            $request->validated('amount'),
            $request->user('super_admin')->id,
        );

        return response()->json(['data' => $this->toResponse($planPrice)], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResponse(PlanPrice $planPrice): array
    {
        return [
            'id' => $planPrice->id,
            'tier' => $planPrice->tier->value,
            'amount' => $planPrice->amount,
            'effectiveFrom' => $planPrice->effectiveFrom->format(DATE_ATOM),
            'effectiveTo' => $planPrice->effectiveTo?->format(DATE_ATOM),
        ];
    }
}
