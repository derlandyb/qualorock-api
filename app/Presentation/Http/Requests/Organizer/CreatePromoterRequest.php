<?php

namespace App\Presentation\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class CreatePromoterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'instagramUrl' => ['required', 'url'],
            'tiktokUrl' => ['required', 'url'],
        ];
    }
}
