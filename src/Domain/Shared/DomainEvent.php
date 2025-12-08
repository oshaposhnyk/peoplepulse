<?php

declare(strict_types=1);

namespace Domain\Shared;

use DateTimeImmutable;
use Domain\Shared\Interfaces\DomainEvent as DomainEventInterface;
use Illuminate\Support\Str;

/**
 * Base class for all domain events
 */
abstract class DomainEvent implements DomainEventInterface
{
    private string $eventId;
    private DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->eventId = (string) Str::uuid();
        $this->occurredAt = new DateTimeImmutable();
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Get event type (e.g., 'employee.hired')
     * Override in child classes
     */
    abstract public function eventType(): string;

    /**
     * Get aggregate ID that produced this event
     */
    abstract public function aggregateId(): string;

    /**
     * Convert event to array for serialization
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'eventId' => $this->eventId,
            'eventType' => $this->eventType(),
            'occurredAt' => $this->occurredAt->format('Y-m-d\TH:i:s.u\Z'),
            'aggregateId' => $this->aggregateId(),
        ];
    }
}

