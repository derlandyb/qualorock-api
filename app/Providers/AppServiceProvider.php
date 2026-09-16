<?php

namespace App\Providers;

use App\Domain\Contracts\OrganizerRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\EloquentOrganizerRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrganizerRepositoryInterface::class, EloquentOrganizerRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
