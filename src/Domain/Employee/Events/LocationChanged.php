<?php

declare(strict_types=1);

namespace Domain\Employee\Events;

use DateTimeImmutable;
use Domain\Employee\ValueObjects\WorkLocation;
use Domain\Shared\DomainEvent;

/**
 * Location changed event
 */
final class LocationChanged extends DomainEvent
{
    public function __construct(
        private readonly string $employeeId,
        private readonly WorkLocation $previousLocation,
        private readonly WorkLocation $newLocation,
        private readonly DateTimeImmutable $effectiveDate,
        private readonly string $reason
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'employee.location_changed';
    }

    public function aggregateId(): string
    {
        return $this->employeeId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'employeeId' => $this->employeeId,
            'previousLocation' => $this->previousLocation->location(),
            'newLocation' => $this->newLocation->location(),
            'effectiveDate' => $this->effectiveDate->format('Y-m-d'),
            'reason' => $this->reason,
        ]);
    }
}

