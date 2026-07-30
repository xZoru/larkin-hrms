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
        // Automatically grant all permissions to Super Admins
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
