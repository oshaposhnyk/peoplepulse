<?php

declare(strict_types=1);

namespace Application\Exceptions;

/**
 * Unauthorized exception
 * 
 * Thrown when user is not authorized to perform action.
 */
class UnauthorizedException extends ApplicationException
{
    public function __construct(
        string $message = 'You are not authorized to perform this action',
        int $code = 403
    ) {
        parent::__construct($message, $code);
    }
}

