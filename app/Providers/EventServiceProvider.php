<?php

declare(strict_types=1);

namespace App\Providers;

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
        // Employee Context Events
        // Domain events will be auto-discovered from Domain\*\Events namespace
        
        // Example manual mapping (if needed):
        // \Domain\Employee\Events\EmployeeHired::class => [
        //     \Application\Listeners\CreateUserAccountListener::class,
        //     \Application\Listeners\InitializeLeaveBalanceListener::class,
        // ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array<int, string>
     */
    protected function discoverEventsWithin(): array
    {
        return [
            $this->app->path('Listeners'),
            base_path('src/Application/Listeners'),
        ];
    }

    /**
     * Configure the proper event listeners for queued events.
     */
    public function boot(): void
    {
        parent::boot();

        // Queue all domain event listeners
        Event::listen(function (\Domain\Shared\Interfaces\DomainEvent $event) {
            // Domain events are handled asynchronously via queue
        });
    }
}

