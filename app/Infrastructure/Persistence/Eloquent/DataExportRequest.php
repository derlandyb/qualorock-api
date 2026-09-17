<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Enums\DataExportRequestStatus;
use Database\Factories\DataExportRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organizer_id', 'status', 'download_path', 'requested_at'])]
class DataExportRequest extends Model
{
    use HasFactory;

    protected static function newFactory(): DataExportRequestFactory
    {
        return DataExportRequestFactory::new();
    }

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
