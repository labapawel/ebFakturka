<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // Bramka dla administratora (dostęp do wszystkiego)
        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        // Definicja bramek
        Gate::define('manage-users', function ($user) {
            return $user->isAdmin() || $user->hasPermission('manage-users');
        });

        Gate::define('issue-invoices', function ($user) {
            return $user->isAdmin() || $user->hasPermission('issue-invoices');
        });

        Gate::define('view-invoices', function ($user) {
            return $user->isAdmin() || $user->hasPermission('view-invoices');
        });

        Gate::define('send-ksef', function ($user) {
            return $user->isAdmin() || $user->hasPermission('send-ksef');
        });

        Gate::define('view-ksef', function ($user) {
            return $user->isAdmin() || $user->hasPermission('view-ksef');
        });
    }
}
