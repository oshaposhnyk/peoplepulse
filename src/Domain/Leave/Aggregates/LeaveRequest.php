<?php

declare(strict_types=1);

namespace Domain\Leave\Aggregates;

use DateTimeImmutable;
use Domain\Leave\Events\LeaveApproved;
use Domain\Leave\Events\LeaveCancelled;
use Domain\Leave\Events\LeaveRejected;
use Domain\Leave\Events\LeaveRequested;
use Domain\Leave\ValueObjects\LeaveId;
use Domain\Leave\ValueObjects\LeavePeriod;
use Domain\Leave\ValueObjects\LeaveStatus;
use Domain\Leave\ValueObjects\LeaveType;
use Domain\Shared\AggregateRoot;
use DomainException;
use InvalidArgumentException;

/**
 * Leave request aggregate root
 */
final class LeaveRequest extends AggregateRoot
{
    public function __construct(
        LeaveId $id,
        string $employeeId,
        LeaveType $type,
        LeavePeriod $period,
        string $reason,
        LeaveStatus $status,
        ?string $approvedBy = null,
        ?DateTimeImmutable $approvedAt = null,
        ?string $rejectionReason = null
    ) {
        $this->id = $id;
        $this->employeeId = $employeeId;
        $this->type = $type;
        $this->period = $period;
        $this->reason = $reason;
        $this->status = $status;
        $this->approvedBy = $approvedBy;
        $this->approvedAt = $approvedAt;
        $this->rejectionReason = $rejectionReason;
    }

    public static function request(
        LeaveId $id,
        string $employeeId,
        LeaveType $type,
        LeavePeriod $period,
        string $reason
    ): self {
        // Business rule: Cannot request leave in the past (except sick leave)
        if (!$type->isSick() && $period->startDate() < new DateTimeImmutable('today')) {
            throw new InvalidArgumentException('Cannot request leave in the past');
        }

        $leave = new self(
            $id,
            $employeeId,
            $type,
            $period,
            $reason,
            LeaveStatus::pending()
        );

        $leave->recordEvent(new LeaveRequested(
            $id->value(),
            $employeeId,
            $type->value(),
            $period,
            $reason
        ));

        return $leave;
    }

    public function approve(string $approvedBy): void
    {
        if (!$this->status->isPending()) {
            throw new DomainException('Can only approve pending leave requests');
        }

        $this->status = LeaveStatus::approved();
        $this->approvedBy = $approvedBy;
        $this->approvedAt = new DateTimeImmutable();

        $this->recordEvent(new LeaveApproved(
            $this->id->value(),
            $this->employeeId,
            $approvedBy,
            $this->period
        ));
    }

    public function reject(string $rejectedBy, string $reason): void
    {
        if (!$this->status->isPending()) {
            throw new DomainException('Can only reject pending leave requests');
        }

        $this->status = LeaveStatus::rejected();
        $this->rejectionReason = $reason;

        $this->recordEvent(new LeaveRejected(
            $this->id->value(),
            $this->employeeId,
            $rejectedBy,
            $reason
        ));
    }

    public function cancel(): void
    {
        if ($this->status->isCompleted()) {
            throw new DomainException('Cannot cancel completed leave');
        }

        // Business rule: Cannot cancel within 24 hours of start
        $now = new DateTimeImmutable();
        $hoursUntilStart = $now->diff($this->period->startDate())->h 
                         + ($now->diff($this->period->startDate())->days * 24);
        
        if ($hoursUntilStart < 24 && $hoursUntilStart > 0) {
            throw new DomainException('Cannot cancel leave within 24 hours of start date');
        }

        $this->status = LeaveStatus::cancelled();

        $this->recordEvent(new LeaveCancelled(
            $this->id->value(),
            $this->employeeId,
            $this->period
        ));
    }

    public function id(): string
    {
        return $this->id->value();
    }

    public function leaveId(): LeaveId
    {
        return $this->id;
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }

    public function type(): LeaveType
    {
        return $this->type;
    }

    public function period(): LeavePeriod
    {
        return $this->period;
    }

    public function status(): LeaveStatus
    {
        return $this->status;
    }

    public function totalDays(): int
    {
        return $this->period->totalDays();
    }
}

