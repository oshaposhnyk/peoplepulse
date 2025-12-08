<?php

declare(strict_types=1);

namespace Domain\Equipment\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Equipment assignment entity
 * 
 * Represents an assignment of equipment to an employee.
 */
final class Assignment
{
    private function __construct(
        private readonly string $employeeId,
        private readonly DateTimeImmutable $assignedAt,
        private ?DateTimeImmutable $returnedAt = null,
        private ?string $returnCondition = null
    ) {
    }

    public static function create(string $employeeId, DateTimeImmutable $assignedAt): self
    {
        return new self($employeeId, $assignedAt);
    }

    public function complete(DateTimeImmutable $returnedAt, string $condition): void
    {
        if ($returnedAt < $this->assignedAt) {
            throw new InvalidArgumentException(
                'Return date cannot be before assignment date'
            );
        }

        $this->returnedAt = $returnedAt;
        $this->returnCondition = $condition;
    }

    public function isActive(): bool
    {
        return $this->returnedAt === null;
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }

    public function assignedAt(): DateTimeImmutable
    {
        return $this->assignedAt;
    }

    public function returnedAt(): ?DateTimeImmutable
    {
        return $this->returnedAt;
    }

    public function returnCondition(): ?string
    {
        return $this->returnCondition;
    }

    public function durationInDays(): ?int
    {
        if (!$this->returnedAt) {
            return null;
        }

        return $this->assignedAt->diff($this->returnedAt)->days;
    }
}

