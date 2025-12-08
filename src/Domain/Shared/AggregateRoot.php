<?php

declare(strict_types=1);

namespace Domain\Shared;

use Domain\Shared\Interfaces\AggregateRoot as AggregateRootInterface;
use Domain\Shared\Traits\RecordsEvents;

/**
 * Base class for all aggregate roots
 * 
 * Provides event recording functionality and enforces aggregate root contract.
 */
abstract class AggregateRoot implements AggregateRootInterface
{
    use RecordsEvents;
    
    /**
     * Get the aggregate root ID
     */
    abstract public function id(): string;
}

