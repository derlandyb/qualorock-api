<?php

namespace App\Providers;

use App\Domain\Contracts\DataExportRequestRepositoryInterface;
use App\Domain\Contracts\EventInfoRequestRepositoryInterface;
use App\Domain\Contracts\EventRepositoryInterface;
use App\Domain\Contracts\OrganizerRepositoryInterface;
use App\Domain\Contracts\PlanPriceRepositoryInterface;
use App\Domain\Contracts\PromoterRepositoryInterface;
use App\Domain\Contracts\VenueRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\EloquentDataExportRequestRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentEventInfoRequestRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentEventRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentOrganizerRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPlanPriceRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPromoterRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentVenueRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrganizerRepositoryInterface::class, EloquentOrganizerRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);
        $this->app->bind(VenueRepositoryInterface::class, EloquentVenueRepository::class);
        $this->app->bind(PromoterRepositoryInterface::class, EloquentPromoterRepository::class);
        $this->app->bind(EventInfoRequestRepositoryInterface::class, EloquentEventInfoRequestRepository::class);
        $this->app->bind(PlanPriceRepositoryInterface::class, EloquentPlanPriceRepository::class);
        $this->app->bind(DataExportRequestRepositoryInterface::class, EloquentDataExportRequestRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
