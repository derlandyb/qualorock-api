<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\DataExportRequest;

interface DataExportRequestRepositoryInterface
{
    public function findById(int $id): ?DataExportRequest;

    /**
     * @return list<DataExportRequest>
     */
    public function findByOrganizerId(int $organizerId): array;

    public function create(DataExportRequest $dataExportRequest): DataExportRequest;

    public function update(int $id, array $attributes): DataExportRequest;

    public function temporaryDownloadUrl(int $id): ?string;

    public function deleteArchive(int $id): void;
}
