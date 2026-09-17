<?php

namespace Database\Factories;

use App\Domain\Enums\DataExportRequestStatus;
use App\Infrastructure\Persistence\Eloquent\DataExportRequest;
use App\Infrastructure\Persistence\Eloquent\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataExportRequest>
 */
class DataExportRequestFactory extends Factory
{
    protected $model = DataExportRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organizer_id' => Organizer::factory(),
            'status' => DataExportRequestStatus::Pending,
            'download_path' => null,
            'requested_at' => now(),
        ];
    }
}
