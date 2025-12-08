<?php

declare(strict_types=1);

namespace Application\Listeners;

use Domain\Shared\Interfaces\DomainEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Base class for domain event listeners
 * 
 * All domain event listeners should extend this class.
 * By default, they are queued for asynchronous processing.
 */
abstract class DomainEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Number of times the job may be attempted
     */
    public int $tries = 3;

    /**
     * Number of seconds to wait before retrying
     * 
     * @var array<int>
     */
    public array $backoff = [60, 120, 300];

    /**
     * Handle the domain event
     */
    abstract public function handle(DomainEvent $event): void;

    /**
     * Determine the queue the listener should be sent to
     */
    public function viaQueue(): string
    {
        return 'default';
    }

    /**
     * Handle a job failure
     */
    public function failed(DomainEvent $event, \Throwable $exception): void
    {
        logger()->error('Domain event listener failed', [
            'listener' => static::class,
            'event' => $event->eventType(),
            'eventId' => $event->eventId(),
            'aggregateId' => $event->aggregateId(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

