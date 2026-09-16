<?php

namespace App\Presentation\Http\Requests\Organizer;

use App\Domain\Contracts\VenueRepositoryInterface;
use App\Domain\Entities\Venue;
use Illuminate\Foundation\Http\FormRequest;

class ShowVenueRequest extends FormRequest
{
    private ?Venue $resolvedVenue = null;

    private bool $venueResolved = false;

    public function authorize(): bool
    {
        return $this->user('organizer') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function venue(): ?Venue
    {
        if (! $this->venueResolved) {
            $this->resolvedVenue = app(VenueRepositoryInterface::class)
                ->findByOrganizerId((int) $this->user('organizer')->id);
            $this->venueResolved = true;
        }

        return $this->resolvedVenue;
    }
}
