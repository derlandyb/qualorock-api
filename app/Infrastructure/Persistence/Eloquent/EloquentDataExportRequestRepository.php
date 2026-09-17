<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Contracts\DataExportRequestRepositoryInterface;
use App\Domain\Entities\DataExportRequest as DataExportRequestEntity;

class EloquentDataExportRequestRepository implements DataExportRequestRepositoryInterface
{
    public function findById(int $id): ?DataExportRequestEntity
    {
        $model = DataExportRequest::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function create(DataExportRequestEntity $dataExportRequest): DataExportRequestEntity
    {
        $model = DataExportRequest::create([
            'organizer_id' => $dataExportRequest->organizerId,
            'status' => $dataExportRequest->status,
            'download_url' => $dataExportRequest->downloadUrl,
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

    private function toEntity(DataExportRequest $model): DataExportRequestEntity
    {
        return new DataExportRequestEntity(
            id: $model->id,
            organizerId: $model->organizer_id,
            status: $model->status,
            downloadUrl: $model->download_url,
            requestedAt: $model->requested_at->toImmutable(),
        );
    }
}
