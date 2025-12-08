<?php

declare(strict_types=1);

namespace Application\Exceptions;

use RuntimeException;

/**
 * Base class for application layer exceptions
 * 
 * Application exceptions represent failures in use case execution.
 */
class ApplicationException extends RuntimeException
{
    public static function create(string $message, int $code = 0): self
    {
        return new static($message, $code);
    }
}

