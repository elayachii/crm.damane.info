<?php

namespace App\Providers;

use App\Http\Responses\Auth\FilamentLoginResponse;
use App\Models\Agency;
use App\Modules\Agencies\Policies\AgencyPolicy;
use App\Modules\Agencies\Repositories\AgencyRepositoryInterface;
use App\Modules\Agencies\Repositories\EloquentAgencyRepository;
use App\Support\AuthFlowTrace;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
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
        $this->app->bind(LoginResponse::class, FilamentLoginResponse::class);

        AuthFlowTrace::info('app_service_provider.register_bindings', [
            'login_response' => FilamentLoginResponse::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Agency::class, AgencyPolicy::class);

        AuthFlowTrace::info('app_service_provider.boot', [
            'agency_policy' => AgencyPolicy::class,
        ]);
    }
}
