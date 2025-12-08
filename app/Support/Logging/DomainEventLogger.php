<?php

declare(strict_types=1);

namespace App\Support\Logging;

use Domain\Shared\Interfaces\DomainEvent;
use Illuminate\Support\Facades\Log;

/**
 * Logger for domain events
 */
class DomainEventLogger
{
    /**
     * Log a domain event
     */
    public static function log(DomainEvent $event): void
    {
        Log::channel('domain')->info($event->eventType(), [
            'event_id' => $event->eventId(),
            'event_type' => $event->eventType(),
            'aggregate_id' => $event->aggregateId(),
            'occurred_at' => $event->occurredAt()->format('Y-m-d H:i:s'),
            'payload' => $event->toArray(),
        ]);
    }
}

