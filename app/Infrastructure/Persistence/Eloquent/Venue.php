<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organizer_id', 'name', 'description', 'address', 'contact_info', 'image_url'])]
class Venue extends Model
{
    use HasFactory;

    protected static function newFactory(): VenueFactory
    {
        return VenueFactory::new();
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }
}
