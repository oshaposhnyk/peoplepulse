<?php

declare(strict_types=1);

namespace Domain\Team\Entities;

use DateTimeImmutable;
use Domain\Team\ValueObjects\MemberRole;
use InvalidArgumentException;

/**
 * Team member entity
 * 
 * Represents an employee's membership in a team.
 */
final class TeamMember
{
    private function __construct(
        private string $employeeId,
        private MemberRole $role,
        private int $allocationPercentage,
        private readonly DateTimeImmutable $assignedAt
    ) {
        $this->validateAllocation($allocationPercentage);
    }

    public static function create(
        string $employeeId,
        MemberRole $role,
        int $allocationPercentage = 100
    ): self {
        return new self(
            $employeeId,
            $role,
            $allocationPercentage,
            new DateTimeImmutable()
        );
    }

    public function changeRole(MemberRole $newRole): void
    {
        $this->role = $newRole;
    }

    public function changeAllocation(int $percentage): void
    {
        $this->validateAllocation($percentage);
        $this->allocationPercentage = $percentage;
    }

    private function validateAllocation(int $percentage): void
    {
        if ($percentage < 1 || $percentage > 100) {
            throw new InvalidArgumentException(
                'Allocation percentage must be between 1 and 100'
            );
        }
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }

    public function role(): MemberRole
    {
        return $this->role;
    }

    public function allocationPercentage(): int
    {
        return $this->allocationPercentage;
    }

    public function assignedAt(): DateTimeImmutable
    {
        return $this->assignedAt;
    }
}

