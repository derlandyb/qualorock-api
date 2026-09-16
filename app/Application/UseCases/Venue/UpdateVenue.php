<?php

namespace App\Application\UseCases\Venue;

use App\Domain\Contracts\VenueRepositoryInterface;
use App\Domain\Entities\Venue;

class UpdateVenue
{
    /**
     * Maps validated camelCase request fields to their snake_case columns -
     * only fields present here can ever reach the repository's update(),
     * so a caller can never forward arbitrary/unvalidated attributes.
     */
    private const array FIELD_MAP = [
        'name' => 'name',
        'description' => 'description',
        'address' => 'address',
        'contactInfo' => 'contact_info',
        'imageUrl' => 'image_url',
    ];

    public function __construct(
        private readonly VenueRepositoryInterface $venues,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(int $venueId, array $data): Venue
    {
        $attributes = [];

        foreach (self::FIELD_MAP as $camelCase => $column) {
            if (array_key_exists($camelCase, $data)) {
                $attributes[$column] = $data[$camelCase];
            }
        }

        return $this->venues->update($venueId, $attributes);
    }
}
