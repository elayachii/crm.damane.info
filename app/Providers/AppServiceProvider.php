<?php

namespace App\Providers;

use App\Models\Agency;
use App\Modules\Agencies\Policies\AgencyPolicy;
use App\Modules\Agencies\Repositories\AgencyRepositoryInterface;
use App\Modules\Agencies\Repositories\EloquentAgencyRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AgencyRepositoryInterface::class, EloquentAgencyRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Agency::class, AgencyPolicy::class);
    }
}
