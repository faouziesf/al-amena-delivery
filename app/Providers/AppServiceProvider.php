<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Package;
use App\Models\User;
use App\Models\Ticket;
use App\Observers\PackageObserver;
use App\Observers\UserObserver;
use App\Observers\TicketObserver;

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
        // Forcer HTTPS si la requête utilise HTTPS (important pour ngrok et proxies)
        if (request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https') {
            \URL::forceScheme('https');
        }

        // Register Observers pour enregistrement automatique et notifications
        Package::observe(PackageObserver::class);
        User::observe(UserObserver::class);
        Ticket::observe(TicketObserver::class);
    }
}
