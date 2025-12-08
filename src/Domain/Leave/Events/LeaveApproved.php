<?php

declare(strict_types=1);

namespace Domain\Leave\Events;

use Domain\Leave\ValueObjects\LeavePeriod;
use Domain\Shared\DomainEvent;

final class LeaveApproved extends DomainEvent
{
    public function __construct(
        private readonly string $leaveId,
        private readonly string $employeeId,
        private readonly string $approvedBy,
        private readonly LeavePeriod $period
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'leave.approved';
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
            'approvedBy' => $this->approvedBy,
            'startDate' => $this->period->startDate()->format('Y-m-d'),
            'endDate' => $this->period->endDate()->format('Y-m-d'),
        ]);
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }
}

