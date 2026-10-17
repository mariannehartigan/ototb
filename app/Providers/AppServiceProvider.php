<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// autologin:
use Illuminate\Support\Facades\Auth;
use App\Models\User;


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
        //
        // autologin:
        if (app()->environment('local')) {
            Auth::loginUsingId(1);
        }
    }
}
