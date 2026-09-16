<?php

namespace App\Presentation\Http\Requests\Organizer;

class DuplicateEventRequest extends OrganizerOwnedEventRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
