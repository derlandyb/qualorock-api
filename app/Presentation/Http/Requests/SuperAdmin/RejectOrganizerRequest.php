<?php

namespace App\Presentation\Http\Requests\SuperAdmin;

use App\Application\Policies\SuperAdminOrganizerPolicy;
use App\Domain\Constants\AdminPanelConstants;
use Illuminate\Foundation\Http\FormRequest;

class RejectOrganizerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(SuperAdminOrganizerPolicy::class)->reject($this->user('super_admin'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:'.AdminPanelConstants::REJECTION_REASON_MAX_LENGTH],
        ];
    }
}
