<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Behind ngrok / reverse proxies, force HTTPS so route() and url() generate https:// links
        if ($this->app->environment('local')) {
            URL::forceScheme('https');
        }
    }
}
