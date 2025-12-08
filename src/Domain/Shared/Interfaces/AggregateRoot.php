<?php

declare(strict_types=1);

namespace Domain\Shared\Interfaces;

/**
 * Marker interface for Aggregate Roots
 * 
 * Aggregate roots are the entry point for all operations on an aggregate.
 * They enforce invariants and business rules.
 */
interface AggregateRoot
{
    /**
     * Get the aggregate root ID
     */
    public function id(): string;
    
    /**
     * Get all recorded domain events
     * 
     * @return array<DomainEvent>
     */
    public function releaseEvents(): array;
}

