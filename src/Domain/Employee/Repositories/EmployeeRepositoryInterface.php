<?php

declare(strict_types=1);

namespace Domain\Employee\Repositories;

use Domain\Employee\Aggregates\Employee;
use Domain\Employee\ValueObjects\EmployeeId;
use Domain\Shared\Interfaces\Repository;
use Domain\Shared\ValueObjects\Email;

/**
 * Employee repository interface
 */
interface EmployeeRepositoryInterface extends Repository
{
    /**
     * Generate next employee ID
     */
    public function nextIdentity(): string;

    /**
     * Save employee aggregate
     */
    public function save(Employee $employee): void;

    /**
     * Find employee by ID
     */
    public function findById(string $id): ?Employee;

    /**
     * Find employee by email
     */
    public function findByEmail(Email $email): ?Employee;

    /**
     * Find all active employees
     * 
     * @return array<Employee>
     */
    public function findActive(): array;

    /**
     * Find employees by position
     * 
     * @return array<Employee>
     */
    public function findByPosition(string $position): array;

    /**
     * Find employees by location
     * 
     * @return array<Employee>
     */
    public function findByLocation(string $location): array;

    /**
     * Check if email exists
     */
    public function emailExists(Email $email): bool;

    /**
     * Delete employee
     */
    public function delete(Employee $employee): void;
}

