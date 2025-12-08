<?php

declare(strict_types=1);

namespace Domain\Leave\Events;

use Domain\Leave\ValueObjects\LeavePeriod;
use Domain\Shared\DomainEvent;

final class LeaveRequested extends DomainEvent
{
    public function __construct(
        private readonly string $leaveId,
        private readonly string $employeeId,
        private readonly string $leaveType,
        private readonly LeavePeriod $period,
        private readonly string $reason
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'leave.requested';
    }

    public function aggregateId(): string
    {
        return $this->leaveId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'leaveId' => $this->leaveId,
            'employeeId' => $this->employeeId,
            'leaveType' => $this->leaveType,
            'startDate' => $this->period->startDate()->format('Y-m-d'),
            'endDate' => $this->period->endDate()->format('Y-m-d'),
            'totalDays' => $this->period->totalDays(),
            'reason' => $this->reason,
        ]);
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }
}

