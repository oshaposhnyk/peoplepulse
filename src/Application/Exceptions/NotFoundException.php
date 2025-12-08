<?php

declare(strict_types=1);

namespace Application\Exceptions;

/**
 * Not found exception
 * 
 * Thrown when requested resource is not found.
 */
class NotFoundException extends ApplicationException
{
    public function __construct(
        string $resourceType,
        string $identifier,
        int $code = 404
    ) {
        parent::__construct(
            "{$resourceType} with identifier '{$identifier}' not found",
            $code
        );
    }

    public static function resource(string $resourceType, string $identifier): self
    {
        return new self($resourceType, $identifier);
    }
}

