<?php

namespace App\Presentation\Http\Requests\Organizer;

use App\Domain\Enums\EventPriceType;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends OrganizerOwnedEventRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'venueId' => ['sometimes', 'integer', Rule::exists('venues', 'id')->where('organizer_id', $this->user('organizer')?->id)],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'dateTime' => ['sometimes', 'date'],
            'location' => ['sometimes', 'string', 'max:255'],
            'fullAddress' => ['sometimes', 'string', 'max:255'],
            'featuredImageUrl' => ['sometimes', 'url'],
            'externalTicketLink' => ['sometimes', 'url'],
            'priceType' => ['sometimes', Rule::enum(EventPriceType::class)],
            'musicCategory' => ['sometimes', 'string', 'max:255'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'ageRange' => ['sometimes', 'nullable', 'string', 'max:255'],
            'additionalInfo' => ['sometimes', 'nullable', 'string'],
            'accessibilityInfo' => ['sometimes', 'nullable', 'string'],
            'eventRules' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
