<?php

namespace App\Presentation\Http\Requests\Organizer;

use App\Domain\Enums\EventPriceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventRequest extends FormRequest
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
            'venueId' => ['required', 'integer', Rule::exists('venues', 'id')->where('organizer_id', $this->user('organizer')?->id)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'dateTime' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'fullAddress' => ['required', 'string', 'max:255'],
            'featuredImageUrl' => ['required', 'url'],
            'externalTicketLink' => ['required', 'url'],
            'priceType' => ['required', Rule::enum(EventPriceType::class)],
            'musicCategory' => ['required', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'ageRange' => ['nullable', 'string', 'max:255'],
            'additionalInfo' => ['nullable', 'string'],
            'accessibilityInfo' => ['nullable', 'string'],
            'eventRules' => ['nullable', 'string'],
        ];
    }
}
