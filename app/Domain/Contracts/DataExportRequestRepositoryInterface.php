<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\DataExportRequest;

interface DataExportRequestRepositoryInterface
{
    public function findById(int $id): ?DataExportRequest;

    public function create(DataExportRequest $dataExportRequest): DataExportRequest;

    public function update(int $id, array $attributes): DataExportRequest;
}
