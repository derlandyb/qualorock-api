<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\PromoterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['organizer_id', 'name', 'phone', 'email', 'instagram_url', 'tiktok_url', 'hidden_at'])]
class Promoter extends Model
{
    use HasFactory;

    protected static function newFactory(): PromoterFactory
    {
        return PromoterFactory::new();
    }

    protected function casts(): array
    {
        return [
            'hidden_at' => 'datetime',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_promoter');
    }
}
