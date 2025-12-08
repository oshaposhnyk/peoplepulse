<?php

declare(strict_types=1);

namespace Domain\Leave\ValueObjects;

/**
 * Leave status value object
 */
final readonly class LeaveStatus
{
    private const PENDING = 'Pending';
    private const APPROVED = 'Approved';
    private const REJECTED = 'Rejected';
    private const CANCELLED = 'Cancelled';
    private const COMPLETED = 'Completed';

    private function __construct(
        private string $status
    ) {
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function approved(): self
    {
        return new self(self::APPROVED);
    }

    public static function rejected(): self
    {
        return new self(self::REJECTED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::COMPLETED;
    }

    public function value(): string
    {
        return $this->status;
    }

    public function __toString(): string
    {
        return $this->status;
    }
}

