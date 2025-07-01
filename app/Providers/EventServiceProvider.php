<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\NewRescueCase::class => [
            \App\Listeners\SendNewRescueCaseNotifications::class,
        ],
        \App\Events\RescueCaseStatusUpdated::class => [
            \App\Listeners\SendRescueCaseStatusUpdate::class,
        ],
    ];
    
    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        // Add any event subscribers here
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
