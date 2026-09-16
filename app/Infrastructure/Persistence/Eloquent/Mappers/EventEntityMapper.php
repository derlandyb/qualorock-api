<?php

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Entities\Event as EventEntity;
use App\Infrastructure\Persistence\Eloquent\Event;

class EventEntityMapper
{
    public static function toEntity(Event $model): EventEntity
    {
        return new EventEntity(
            id: $model->id,
            organizerId: $model->organizer_id,
            venueId: $model->venue_id,
            title: $model->title,
            description: $model->description,
            dateTime: $model->date_time->toImmutable(),
            location: $model->location,
            fullAddress: $model->full_address,
            featuredImageUrl: $model->featured_image_url,
            externalTicketLink: $model->external_ticket_link,
            priceType: $model->price_type,
            musicCategory: $model->music_category,
            capacity: $model->capacity,
            ageRange: $model->age_range,
            additionalInfo: $model->additional_info,
            accessibilityInfo: $model->accessibility_info,
            eventRules: $model->event_rules,
            status: $model->status,
            publishedAt: $model->published_at?->toImmutable(),
        );
    }
}
