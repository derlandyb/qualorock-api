<?php

namespace App\Presentation\Http\Requests\Organizer;

use App\Application\Policies\EventPolicy;
use App\Domain\Contracts\EventInfoRequestRepositoryInterface;
use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Entities\EventInfoRequest;
use Illuminate\Foundation\Http\FormRequest;

class RespondToEventInfoRequestRequest extends FormRequest
{
    private ?EventInfoRequest $resolvedEventInfoRequest = null;

    private bool $eventInfoRequestResolved = false;

    public function authorize(): bool
    {
        $eventInfoRequest = $this->eventInfoRequest();

        if ($eventInfoRequest === null) {
            return false;
        }

        $event = app(EventRepositoryInterface::class)->findById($eventInfoRequest->eventId);

        return $event !== null && app(EventPolicy::class)->owns($this->user('organizer'), $event);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'response' => ['required', 'string'],
        ];
    }

    public function eventInfoRequest(): ?EventInfoRequest
    {
        if (! $this->eventInfoRequestResolved) {
            $this->resolvedEventInfoRequest = app(EventInfoRequestRepositoryInterface::class)
                ->findById((int) $this->route('infoRequest'));
            $this->eventInfoRequestResolved = true;
        }

        return $this->resolvedEventInfoRequest;
    }
}
