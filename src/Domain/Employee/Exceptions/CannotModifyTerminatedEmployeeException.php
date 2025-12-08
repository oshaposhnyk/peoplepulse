<?php

declare(strict_types=1);

namespace Domain\Employee\Exceptions;

use Domain\Shared\Exceptions\DomainException;

/**
 * Exception thrown when attempting to modify a terminated employee
 */
final class CannotModifyTerminatedEmployeeException extends DomainException
{
}

