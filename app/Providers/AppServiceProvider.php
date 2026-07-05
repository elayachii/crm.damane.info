<?php

namespace App\Providers;

use App\Http\Responses\Auth\FilamentLoginResponse;
use App\Models\Agency;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\User;
use App\Modules\Agencies\Policies\AgencyPolicy;
use App\Modules\Agencies\Repositories\AgencyRepositoryInterface;
use App\Modules\Agencies\Repositories\EloquentAgencyRepository;
use App\Modules\Customers\Policies\CustomerPolicy;
use App\Modules\Invoices\Policies\InvoicePolicy;
use App\Modules\Payments\Policies\PaymentPolicy;
use App\Modules\Subscriptions\Policies\SubscriptionPolicy;
use App\Modules\Tickets\Policies\TicketPolicy;
use App\Modules\Users\Policies\UserPolicy;
use App\Policies\RolePolicy;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AgencyRepositoryInterface::class, EloquentAgencyRepository::class);
        $this->app->bind(LoginResponse::class, FilamentLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(static function (User $user): ?bool {
            return $user->isSuperAdmin() ? true : null;
        });

        Gate::policy(Agency::class, AgencyPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
    }
}
