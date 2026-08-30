<?php

namespace App\Providers;

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
        // Force HTTPS in all environments to fix Livewire Mixed Content issues
        // behind Cloudflare/Nginx proxies, even if APP_ENV is incorrectly set to local
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}
