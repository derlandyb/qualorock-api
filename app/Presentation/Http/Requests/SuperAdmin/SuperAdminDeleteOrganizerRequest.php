<?php

namespace App\Presentation\Http\Requests\SuperAdmin;

use App\Application\Policies\SuperAdminOrganizerPolicy;
use Illuminate\Foundation\Http\FormRequest;

class SuperAdminDeleteOrganizerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(SuperAdminOrganizerPolicy::class)->delete($this->user('super_admin'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'confirm' => ['sometimes', 'boolean'],
        ];
    }

    public function confirmed(): bool
    {
        return (bool) $this->boolean('confirm');
    }
}
