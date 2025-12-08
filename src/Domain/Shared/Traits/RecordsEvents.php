<?php

declare(strict_types=1);

namespace Domain\Shared\Traits;

use Domain\Shared\Interfaces\DomainEvent;

/**
 * Trait for recording domain events in aggregates
 */
trait RecordsEvents
{
    /** @var array<DomainEvent> */
    private array $recordedEvents = [];
    
    /**
     * Record a domain event
     */
    protected function recordEvent(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
    }
    
    /**
     * Get all recorded events and clear them
     * 
     * @return array<DomainEvent>
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        
        return $events;
    }
    
    /**
     * Get recorded events without clearing them
     * 
     * @return array<DomainEvent>
     */
    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }
}

