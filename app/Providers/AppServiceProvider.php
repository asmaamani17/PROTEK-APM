<?php

namespace App\Providers;

use App\Services\SimpleSmsService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SimpleSmsService::class, function ($app) {
            return new SimpleSmsService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom notification channels
        Notification::extend('sms', function ($app) {
            return new \App\Notifications\Channels\SmsChannel();
        });
        
        // Set default string length for database migrations
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
    }
}
