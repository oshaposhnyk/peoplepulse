<?php

declare(strict_types=1);

namespace Domain\Team\Exceptions;

use Domain\Shared\Exceptions\DomainException;

/**
 * Exception thrown when team size limit is exceeded
 */
final class TeamSizeLimitExceededException extends DomainException
{
}

