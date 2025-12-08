<?php

declare(strict_types=1);

namespace Domain\Employee\Events;

use DateTimeImmutable;
use Domain\Employee\ValueObjects\Position;
use Domain\Employee\ValueObjects\Salary;
use Domain\Shared\DomainEvent;

/**
 * Position changed event
 */
final class PositionChanged extends DomainEvent
{
    public function __construct(
        private readonly string $employeeId,
        private readonly Position $previousPosition,
        private readonly Position $newPosition,
        private readonly Salary $previousSalary,
        private readonly Salary $newSalary,
        private readonly DateTimeImmutable $effectiveDate,
        private readonly string $reason
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'employee.position_changed';
    }

    public function aggregateId(): string
    {
        return $this->employeeId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'employeeId' => $this->employeeId,
            'previousPosition' => $this->previousPosition->title(),
            'newPosition' => $this->newPosition->title(),
            'previousSalary' => $this->previousSalary->amount(),
            'newSalary' => $this->newSalary->amount(),
            'effectiveDate' => $this->effectiveDate->format('Y-m-d'),
            'reason' => $this->reason,
        ]);
    }
}

