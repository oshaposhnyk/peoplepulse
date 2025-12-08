<?php

declare(strict_types=1);

namespace Domain\Shared\Exceptions;

use DomainException as BaseDomainException;

/**
 * Base class for all domain exceptions
 * 
 * Domain exceptions represent violations of business rules.
 */
class DomainException extends BaseDomainException
{
    public static function create(string $message, int $code = 0): self
    {
        return new static($message, $code);
    }
}

