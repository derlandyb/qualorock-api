<?php

namespace App\Presentation\Http\Requests\Organizer;

use App\Application\Policies\PromoterPolicy;
use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Entities\Promoter;
use Illuminate\Foundation\Http\FormRequest;

abstract class OrganizerOwnedPromoterRequest extends FormRequest
{
    private ?Promoter $resolvedPromoter = null;

    private bool $promoterResolved = false;

    public function authorize(): bool
    {
        $promoter = $this->promoter();

        return $promoter !== null && app(PromoterPolicy::class)->owns($this->user('organizer'), $promoter);
    }

    public function promoter(): ?Promoter
    {
        if (! $this->promoterResolved) {
            $this->resolvedPromoter = app(PromoterRepositoryInterface::class)->findById((int) $this->route('promoter'));
            $this->promoterResolved = true;
        }

        return $this->resolvedPromoter;
    }
}
