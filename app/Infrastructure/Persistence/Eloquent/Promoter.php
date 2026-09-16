<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['organizer_id', 'name', 'phone', 'email', 'instagram_url', 'tiktok_url'])]
class Promoter extends Model
{
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_promoter');
    }
}
