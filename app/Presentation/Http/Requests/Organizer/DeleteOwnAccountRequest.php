<?php

namespace App\Presentation\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class DeleteOwnAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('organizer') !== null;
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
