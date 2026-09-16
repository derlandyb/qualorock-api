<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Enums\DataExportRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organizer_id', 'status', 'download_url', 'requested_at'])]
class DataExportRequest extends Model
{
    protected function casts(): array
    {
        return [
            'status' => DataExportRequestStatus::class,
            'requested_at' => 'datetime',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }
}
