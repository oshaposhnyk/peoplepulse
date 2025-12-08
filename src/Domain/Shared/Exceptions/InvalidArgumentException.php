<?php

declare(strict_types=1);

namespace Domain\Shared\Exceptions;

use InvalidArgumentException as BaseInvalidArgumentException;

/**
 * Invalid argument exception for domain
 * 
 * Thrown when method receives invalid argument that violates domain rules.
 */
class InvalidArgumentException extends BaseInvalidArgumentException
{
    public static function create(string $message, int $code = 0): self
    {
        return new static($message, $code);
    }
}

