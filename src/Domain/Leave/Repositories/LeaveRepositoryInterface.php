<?php

declare(strict_types=1);

namespace Domain\Leave\Repositories;

use DateTimeImmutable;
use Domain\Leave\Aggregates\LeaveRequest;
use Domain\Leave\ValueObjects\LeaveId;
use Domain\Shared\Interfaces\Repository;

/**
 * Leave repository interface
 */
interface LeaveRepositoryInterface extends Repository
{
    public function nextIdentity(): string;

    /**
     * @return LeaveRequest|null
     */
    public function findById(string $id): mixed;

    /**
     * Find leave requests by employee
     * 
     * @return array<LeaveRequest>
     */
    public function findByEmployee(string $employeeId): array;

    /**
     * Find pending leave requests
     * 
     * @return array<LeaveRequest>
     */
    public function findPending(): array;

    /**
     * Find approved leave in date range
     * 
     * @return array<LeaveRequest>
     */
    public function findApprovedInPeriod(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array;

    /**
     * Check if employee has overlapping leave
     */
    public function hasOverlappingLeave(
        string $employeeId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): bool;

}

