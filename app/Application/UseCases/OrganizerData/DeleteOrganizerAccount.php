<?php

namespace App\Application\UseCases\OrganizerData;

use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Contracts\OrganizerRepositoryInterface;
use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Contracts\VenueRepositoryInterface;
use App\Domain\Exceptions\OrganizerHasUpcomingPublishedEventException;
use Illuminate\Support\Carbon;

class DeleteOrganizerAccount
{
    public function __construct(
        private readonly OrganizerRepositoryInterface $organizers,
        private readonly VenueRepositoryInterface $venues,
        private readonly EventRepositoryInterface $events,
        private readonly PromoterRepositoryInterface $promoters,
    ) {}

    public function handle(int $organizerId, bool $confirm): void
    {
        $venue = $this->venues->findByOrganizerId($organizerId);

        if ($venue !== null && $this->venues->findUpcomingPublishedEvents($venue->id) !== [] && ! $confirm) {
            throw new OrganizerHasUpcomingPublishedEventException;
        }

        $this->organizers->softDelete($organizerId);

        $hiddenAt = Carbon::now();

        if ($venue !== null) {
            $this->venues->update($venue->id, ['hidden_at' => $hiddenAt]);
        }

        foreach ($this->events->findByOrganizerId($organizerId) as $event) {
            $this->events->update($event->id, ['hidden_at' => $hiddenAt]);
        }

        foreach ($this->promoters->findByOrganizerId($organizerId) as $promoter) {
            $this->promoters->update($promoter->id, ['hidden_at' => $hiddenAt]);
        }
    }
}
