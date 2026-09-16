<?php

namespace App\Presentation\Http\Requests\Organizer;

use App\Application\Policies\EventPolicy;
use App\Application\Policies\PromoterPolicy;
use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Entities\Event;
use App\Domain\Entities\Promoter;
use Illuminate\Foundation\Http\FormRequest;

class LinkPromoterRequest extends FormRequest
{
    private ?Promoter $resolvedPromoter = null;

    private bool $promoterResolved = false;

    private ?Event $resolvedEvent = null;

    private bool $eventResolved = false;

    public function authorize(): bool
    {
        $promoter = $this->promoter();
        $event = $this->event();

        return $promoter !== null && $event !== null
            && app(PromoterPolicy::class)->owns($this->user('organizer'), $promoter)
            && app(EventPolicy::class)->owns($this->user('organizer'), $event);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function promoter(): ?Promoter
    {
        if (! $this->promoterResolved) {
            $this->resolvedPromoter = app(PromoterRepositoryInterface::class)->findById((int) $this->route('promoter'));
            $this->promoterResolved = true;
        }

        return $this->resolvedPromoter;
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
