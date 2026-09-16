<?php

namespace App\Presentation\Http\Requests\Organizer;

class UpdateVenueRequest extends ShowVenueRequest
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->venue() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'address' => ['sometimes', 'string', 'max:255'],
            'contactInfo' => ['sometimes', 'string', 'max:255'],
            'imageUrl' => ['sometimes', 'nullable', 'url'],
        ];
    }
}
