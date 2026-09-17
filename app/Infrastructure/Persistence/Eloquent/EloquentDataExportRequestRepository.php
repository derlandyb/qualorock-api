<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Contracts\DataExportRequestRepositoryInterface;
use App\Domain\Entities\DataExportRequest as DataExportRequestEntity;
use Illuminate\Support\Facades\Storage;

class EloquentDataExportRequestRepository implements DataExportRequestRepositoryInterface
{
    public function findById(int $id): ?DataExportRequestEntity
    {
        $model = DataExportRequest::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByOrganizerId(int $organizerId): array
    {
        return DataExportRequest::where('organizer_id', $organizerId)
            ->get()
            ->map(fn (DataExportRequest $model) => $this->toEntity($model))
            ->all();
    }

    public function create(DataExportRequestEntity $dataExportRequest): DataExportRequestEntity
    {
        $model = DataExportRequest::create([
            'organizer_id' => $dataExportRequest->organizerId,
            'status' => $dataExportRequest->status,
            'download_path' => $dataExportRequest->downloadPath,
            'requested_at' => $dataExportRequest->requestedAt,
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, array $attributes): DataExportRequestEntity
    {
        $model = DataExportRequest::findOrFail($id);
        $model->update($attributes);

        return $this->toEntity($model);
    }

    public function temporaryDownloadUrl(int $id): ?string
    {
        $model = DataExportRequest::findOrFail($id);

        if ($model->download_path === null) {
            return null;
        }

        return Storage::disk('s3')->temporaryUrl(
            $model->download_path,
            now()->addMinutes(AdminPanelConstants::DATA_EXPORT_DOWNLOAD_URL_TTL_MINUTES),
        );
    }

    public function deleteArchive(int $id): void
    {
        $model = DataExportRequest::findOrFail($id);

        if ($model->download_path !== null) {
            Storage::disk('s3')->delete($model->download_path);
            $model->update(['download_path' => null]);
        }
    }

    private function toEntity(DataExportRequest $model): DataExportRequestEntity
    {
        return new DataExportRequestEntity(
            id: $model->id,
            organizerId: $model->organizer_id,
            status: $model->status,
            downloadPath: $model->download_path,
            requestedAt: $model->requested_at->toImmutable(),
        );
    }
}
