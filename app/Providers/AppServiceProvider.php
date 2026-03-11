<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\QueueEntry;
use App\Models\EventCategory;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\TicketTypeQuota;
use App\Models\VenueType;
use App\Models\OrderItem;
use App\Models\User;
use App\Observers\TicketTypeQuotaObserver;
use App\Observers\EventObserver;
use App\Observers\UserObserver;
use App\Policies\EventCategoryPolicy;
use App\Policies\EventPolicy;
use App\Policies\QueueEntryPolicy;
use App\Policies\TicketPolicy;
use App\Policies\TicketTypeQuotaPolicy;
use App\Policies\TicketTypePolicy;
use App\Policies\VenueTypePolicy;
use App\Policies\OrderItemPolicy;
use App\Policies\OrderPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TicketTypeQuota::observe(TicketTypeQuotaObserver::class);
        \App\Models\Event::observe(EventObserver::class);
        User::observe(UserObserver::class);

        Gate::define('admin', fn (User $user): bool => $user->isAdmin());
        Gate::policy(\App\Models\Event::class, EventPolicy::class);
        Gate::policy(EventCategory::class, EventCategoryPolicy::class);
        Gate::policy(VenueType::class, VenueTypePolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(TicketType::class, TicketTypePolicy::class);
        Gate::policy(TicketTypeQuota::class, TicketTypeQuotaPolicy::class);
        Gate::policy(QueueEntry::class, QueueEntryPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(OrderItem::class, OrderItemPolicy::class);

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
