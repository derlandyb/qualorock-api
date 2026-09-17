<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\EventInfoRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['event_id', 'consumer_user_id', 'message', 'organizer_response', 'responded_at'])]
class EventInfoRequest extends Model
{
    use HasFactory;

    protected static function newFactory(): EventInfoRequestFactory
    {
        return EventInfoRequestFactory::new();
    }

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function respond(string $response): void
    {
        $this->update([
            'organizer_response' => $response,
            'responded_at' => Carbon::now(),
        ]);
    }
}
