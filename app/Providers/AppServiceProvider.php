<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        if (env('AUTO_MIGRATE_ON_BOOT', false)) {
            $this->runAutoMigrations();
        }

        // Bramka dla administratora (dostep do wszystkiego)
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

    private function runAutoMigrations(): void
    {
        try {
            if (!Schema::hasTable('migrations')) {
                return;
            }

            if (Schema::hasTable('invoice_counters')) {
                return;
            }

            Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
        } catch (\Throwable $exception) {
            Log::warning('Auto-migrate failed: ' . $exception->getMessage());
        }
    }
}
