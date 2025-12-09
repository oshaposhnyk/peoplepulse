<?php

declare(strict_types=1);

namespace Domain\Employee\Events;

use DateTimeImmutable;
use Domain\Shared\DomainEvent;

/**
 * Employee reinstated event
 * 
 * Triggered when a terminated employee is reinstated.
 */
final class EmployeeReinstated extends DomainEvent
{
    public function __construct(
        private readonly string $employeeId,
        private readonly DateTimeImmutable $reinstatementDate,
        private readonly string $reason
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'employee.reinstated';
    }

    public function aggregateId(): string
    {
        return $this->employeeId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'employeeId' => $this->employeeId,
            'reinstatementDate' => $this->reinstatementDate->format('Y-m-d'),
            'reason' => $this->reason,
        ]);
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }

    public function reinstatementDate(): DateTimeImmutable
    {
        return $this->reinstatementDate;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}

