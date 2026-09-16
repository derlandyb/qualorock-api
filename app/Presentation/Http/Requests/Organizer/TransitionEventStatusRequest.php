<?php

namespace App\Presentation\Http\Requests\Organizer;

use App\Domain\Enums\EventStatus;
use Illuminate\Validation\Rule;

class TransitionEventStatusRequest extends OrganizerOwnedEventRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(EventStatus::class)],
        ];
    }

    public function targetStatus(): EventStatus
    {
        return EventStatus::from($this->validated('status'));
    }
}
