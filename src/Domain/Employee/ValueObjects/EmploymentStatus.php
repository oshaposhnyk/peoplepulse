<?php

declare(strict_types=1);

namespace Domain\Employee\ValueObjects;

/**
 * Employment status value object
 */
final readonly class EmploymentStatus
{
    private const ACTIVE = 'Active';
    private const TERMINATED = 'Terminated';
    private const ON_LEAVE = 'OnLeave';

    private function __construct(
        private string $status
    ) {
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function terminated(): self
    {
        return new self(self::TERMINATED);
    }

    public static function onLeave(): self
    {
        return new self(self::ON_LEAVE);
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function isTerminated(): bool
    {
        return $this->status === self::TERMINATED;
    }

    public function isOnLeave(): bool
    {
        return $this->status === self::ON_LEAVE;
    }

    public function value(): string
    {
        return $this->status;
    }

    public function equals(EmploymentStatus $other): bool
    {
        return $this->status === $other->status;
    }

    public function __toString(): string
    {
        return $this->status;
    }
}

