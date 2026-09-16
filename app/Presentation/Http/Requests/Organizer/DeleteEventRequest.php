<?php

namespace App\Presentation\Http\Requests\Organizer;

class DeleteEventRequest extends OrganizerOwnedEventRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
