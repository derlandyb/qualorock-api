<?php

namespace App\Presentation\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class RejectOrganizerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
