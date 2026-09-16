<?php

namespace App\Presentation\Http\Requests\Organizer;

class UpdatePromoterRequest extends OrganizerOwnedPromoterRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email'],
            'instagramUrl' => ['sometimes', 'url'],
            'tiktokUrl' => ['sometimes', 'url'],
        ];
    }
}
