<?php

namespace App\Presentation\Http\Requests\SuperAdmin;

use App\Application\Policies\PlanPricingPolicy;
use App\Domain\Constants\AdminPanelConstants;
use Illuminate\Foundation\Http\FormRequest;

class SetPlanPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(PlanPricingPolicy::class)->set($this->user('super_admin'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1', 'max:'.AdminPanelConstants::PLAN_PRICE_MAX_CENTS],
        ];
    }
}
