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
    public function save(mixed $aggregate): void;
    
    /**
     * Find aggregate by ID
     */
    public function findById(string $id): mixed;
    
    /**
     * Delete aggregate
     */
    public function delete(mixed $aggregate): void;
}

