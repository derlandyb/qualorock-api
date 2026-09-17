<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Enums\EventPriceType;
use App\Domain\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'organizer_id', 'venue_id', 'title', 'description', 'date_time', 'location', 'full_address',
    'featured_image_url', 'external_ticket_link', 'price_type', 'music_category', 'capacity',
    'age_range', 'additional_info', 'accessibility_info', 'event_rules', 'status', 'published_at', 'hidden_at',
])]
class Event extends Model
{
    use HasFactory;

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }

    protected function casts(): array
    {
        return [
            'date_time' => 'datetime',
            'price_type' => EventPriceType::class,
            'status' => EventStatus::class,
            'published_at' => 'datetime',
            'hidden_at' => 'datetime',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function promoters(): BelongsToMany
    {
        return $this->belongsToMany(Promoter::class, 'event_promoter');
    }
}
