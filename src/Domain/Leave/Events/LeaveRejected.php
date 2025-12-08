<?php

declare(strict_types=1);

namespace Domain\Leave\Events;

use Domain\Shared\DomainEvent;

final class LeaveRejected extends DomainEvent
{
    public function __construct(
        private readonly string $leaveId,
        private readonly string $employeeId,
        private readonly string $rejectedBy,
        private readonly string $reason
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'leave.rejected';
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
            'rejectedBy' => $this->rejectedBy,
            'reason' => $this->reason,
        ]);
    }
}

