<?php

namespace App\Presentation\Http\Requests\Organizer;

use App\Application\Policies\EventPolicy;
use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\Event;
use Illuminate\Foundation\Http\FormRequest;

abstract class OrganizerOwnedEventRequest extends FormRequest
{
    private ?Event $resolvedEvent = null;

    private bool $eventResolved = false;

    public function authorize(): bool
    {
        $event = $this->event();

        return $event !== null && app(EventPolicy::class)->owns($this->user('organizer'), $event);
    }

    public function event(): ?Event
    {
        if (! $this->eventResolved) {
            $this->resolvedEvent = app(EventRepositoryInterface::class)->findById((int) $this->route('event'));
            $this->eventResolved = true;
        }

        return $this->resolvedEvent;
    }
}
