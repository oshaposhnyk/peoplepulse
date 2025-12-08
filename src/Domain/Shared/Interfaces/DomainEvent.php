<?php

declare(strict_types=1);

namespace Domain\Shared\Interfaces;

use DateTimeImmutable;

/**
 * Interface for all domain events
 * 
 * Domain events represent something that has happened in the domain.
 * They are immutable and named in past tense.
 */
interface DomainEvent
{
    /**
     * Get unique event ID
     */
    public function eventId(): string;
    
    /**
     * Get event type (e.g., 'employee.hired')
     */
    public function eventType(): string;
    
    /**
     * Get when the event occurred
     */
    public function occurredAt(): DateTimeImmutable;
    
    /**
     * Get the aggregate ID that produced this event
     */
    public function aggregateId(): string;
    
    /**
     * Convert event to array for serialization
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array;
}

