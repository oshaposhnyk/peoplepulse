<?php

declare(strict_types=1);

namespace Domain\Leave\Events;

use Domain\Leave\ValueObjects\LeavePeriod;
use Domain\Shared\DomainEvent;

final class LeaveCancelled extends DomainEvent
{
    public function __construct(
        private readonly string $leaveId,
        private readonly string $employeeId,
        private readonly LeavePeriod $period
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'leave.cancelled';
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
            'totalDays' => $this->period->totalDays(),
        ]);
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }

    public function totalDays(): int
    {
        return $this->period->totalDays();
    }
}

