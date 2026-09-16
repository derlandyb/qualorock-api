<?php

namespace App\Presentation\Http\Requests\Organizer;

use App\Application\Policies\EventPolicy;
use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\Event;

class LinkPromoterRequest extends OrganizerOwnedPromoterRequest
{
    private ?Event $resolvedEvent = null;

    private bool $eventResolved = false;

    public function authorize(): bool
    {
        $event = $this->event();

        return parent::authorize() && $event !== null
            && app(EventPolicy::class)->owns($this->user('organizer'), $event);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
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
