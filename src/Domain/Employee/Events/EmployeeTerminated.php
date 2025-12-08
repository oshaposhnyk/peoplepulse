<?php

declare(strict_types=1);

namespace Domain\Employee\Events;

use DateTimeImmutable;
use Domain\Shared\DomainEvent;

/**
 * Employee terminated event
 * 
 * Triggered when an employee's employment is terminated.
 */
final class EmployeeTerminated extends DomainEvent
{
    public function __construct(
        private readonly string $employeeId,
        private readonly DateTimeImmutable $terminationDate,
        private readonly DateTimeImmutable $lastWorkingDay,
        private readonly string $terminationType,
        private readonly string $reason
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'employee.terminated';
    }

    public function aggregateId(): string
    {
        return $this->employeeId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'employeeId' => $this->employeeId,
            'terminationDate' => $this->terminationDate->format('Y-m-d'),
            'lastWorkingDay' => $this->lastWorkingDay->format('Y-m-d'),
            'terminationType' => $this->terminationType,
            'reason' => $this->reason,
        ]);
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }

    public function terminationDate(): DateTimeImmutable
    {
        return $this->terminationDate;
    }

    public function lastWorkingDay(): DateTimeImmutable
    {
        return $this->lastWorkingDay;
    }

    public function terminationType(): string
    {
        return $this->terminationType;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}

