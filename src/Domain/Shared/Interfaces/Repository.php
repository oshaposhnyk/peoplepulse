<?php

declare(strict_types=1);

namespace Domain\Shared\Interfaces;

/**
 * Base repository interface
 * 
 * All domain repositories should extend this interface.
 */
interface Repository
{
    /**
     * Generate next identity for new aggregate
     */
    public function nextIdentity(): string;
    
    /**
     * Save aggregate
     */
    public function save(AggregateRoot $aggregate): void;
    
    /**
     * Find aggregate by ID
     */
    public function findById(string $id): ?AggregateRoot;
    
    /**
     * Delete aggregate
     */
    public function delete(AggregateRoot $aggregate): void;
}

