<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\EventStatsFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_id', 'views_count', 'favorites_count', 'ticket_link_clicks_count', 'interest_count'])]
class EventStats extends Model
{
    use HasFactory;

    protected static function newFactory(): EventStatsFactory
    {
        return EventStatsFactory::new();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
