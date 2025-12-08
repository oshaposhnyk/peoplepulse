<?php

declare(strict_types=1);

namespace Domain\Employee\Aggregates;

use DateTimeImmutable;
use Domain\Employee\Events\EmployeeHired;
use Domain\Employee\Events\EmployeeTerminated;
use Domain\Employee\Events\LocationChanged;
use Domain\Employee\Events\PositionChanged;
use Domain\Employee\Events\RemoteWorkConfigured;
use Domain\Employee\Exceptions\CannotModifyTerminatedEmployeeException;
use Domain\Employee\ValueObjects\EmployeeId;
use Domain\Employee\ValueObjects\EmploymentStatus;
use Domain\Employee\ValueObjects\PersonalInfo;
use Domain\Employee\ValueObjects\Position;
use Domain\Employee\ValueObjects\RemoteWorkPolicy;
use Domain\Employee\ValueObjects\Salary;
use Domain\Employee\ValueObjects\WorkLocation;
use Domain\Shared\AggregateRoot;
use InvalidArgumentException;

/**
 * Employee aggregate root
 * 
 * Manages the complete lifecycle of an employee from hire to termination.
 */
final class Employee extends AggregateRoot
{
    private function __construct(
        private EmployeeId $id,
        private PersonalInfo $personalInfo,
        private Position $position,
        private Salary $salary,
        private WorkLocation $location,
        private DateTimeImmutable $hireDate,
        private EmploymentStatus $status,
        private ?RemoteWorkPolicy $remoteWorkPolicy = null,
        private ?DateTimeImmutable $terminationDate = null,
        private array $positionHistory = [],
        private array $locationHistory = []
    ) {
    }

    /**
     * Factory method: Hire a new employee
     */
    public static function hire(
        EmployeeId $id,
        PersonalInfo $personalInfo,
        Position $position,
        Salary $salary,
        WorkLocation $location,
        DateTimeImmutable $hireDate
    ): self {
        // Business rule: Hire date cannot be in the future
        if ($hireDate > new DateTimeImmutable()) {
            throw new InvalidArgumentException('Hire date cannot be in the future');
        }

        $employee = new self(
            $id,
            $personalInfo,
            $position,
            $salary,
            $location,
            $hireDate,
            EmploymentStatus::active()
        );

        // Raise domain event
        $employee->recordEvent(new EmployeeHired(
            $id->value(),
            $personalInfo,
            $position,
            $salary,
            $location,
            $hireDate
        ));

        return $employee;
    }

    /**
     * Update personal information
     */
    public function updatePersonalInfo(PersonalInfo $newInfo): void
    {
        $this->ensureNotTerminated();
        $this->personalInfo = $newInfo;
    }

    /**
     * Change position (promotion, demotion, role change)
     */
    public function changePosition(
        Position $newPosition,
        Salary $newSalary,
        DateTimeImmutable $effectiveDate,
        string $reason
    ): void {
        $this->ensureNotTerminated();

        // Business rule: Position effective date cannot be in the past
        $today = new DateTimeImmutable('today');
        if ($effectiveDate < $today) {
            throw new InvalidArgumentException('Position effective date cannot be in the past');
        }

        // Business rule: Salary can only increase or stay same
        if (!$this->salary->canIncreaseTo($newSalary)) {
            throw new InvalidArgumentException(
                'Salary decrease requires special approval process'
            );
        }

        $previousPosition = $this->position;
        $previousSalary = $this->salary;

        $this->position = $newPosition;
        $this->salary = $newSalary;

        // Raise domain event
        $this->recordEvent(new PositionChanged(
            $this->id->value(),
            $previousPosition,
            $newPosition,
            $previousSalary,
            $newSalary,
            $effectiveDate,
            $reason
        ));
    }

    /**
     * Change office location
     */
    public function changeLocation(
        WorkLocation $newLocation,
        DateTimeImmutable $effectiveDate,
        string $reason
    ): void {
        $this->ensureNotTerminated();

        $previousLocation = $this->location;
        $this->location = $newLocation;

        $this->recordEvent(new LocationChanged(
            $this->id->value(),
            $previousLocation,
            $newLocation,
            $effectiveDate,
            $reason
        ));
    }

    /**
     * Configure remote work
     */
    public function configureRemoteWork(?RemoteWorkPolicy $policy): void
    {
        $this->ensureNotTerminated();

        $this->remoteWorkPolicy = $policy;

        $this->recordEvent(new RemoteWorkConfigured(
            $this->id->value(),
            $policy
        ));
    }

    /**
     * Terminate employment
     */
    public function terminate(
        DateTimeImmutable $terminationDate,
        DateTimeImmutable $lastWorkingDay,
        string $terminationType,
        string $reason
    ): void {
        $this->ensureNotTerminated();

        // Business rule: Termination date cannot be in the past
        $today = new DateTimeImmutable('today');
        if ($terminationDate < $today) {
            throw new InvalidArgumentException('Termination date cannot be in the past');
        }

        // Business rule: Last working day must be on or before termination date
        if ($lastWorkingDay > $terminationDate) {
            throw new InvalidArgumentException(
                'Last working day must be on or before termination date'
            );
        }

        $this->status = EmploymentStatus::terminated();
        $this->terminationDate = $terminationDate;

        $this->recordEvent(new EmployeeTerminated(
            $this->id->value(),
            $terminationDate,
            $lastWorkingDay,
            $terminationType,
            $reason
        ));
    }

    /**
     * Get employee ID
     */
    public function id(): string
    {
        return $this->id->value();
    }

    /**
     * Check if employee is active
     */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Check if employee is terminated
     */
    public function isTerminated(): bool
    {
        return $this->status->isTerminated();
    }

    /**
     * Ensure employee is not terminated
     */
    private function ensureNotTerminated(): void
    {
        if ($this->isTerminated()) {
            throw new CannotModifyTerminatedEmployeeException(
                "Cannot modify terminated employee {$this->id->value()}"
            );
        }
    }

    // Getters
    public function employeeId(): EmployeeId
    {
        return $this->id;
    }

    public function personalInfo(): PersonalInfo
    {
        return $this->personalInfo;
    }

    public function position(): Position
    {
        return $this->position;
    }

    public function salary(): Salary
    {
        return $this->salary;
    }

    public function location(): WorkLocation
    {
        return $this->location;
    }

    public function hireDate(): DateTimeImmutable
    {
        return $this->hireDate;
    }

    public function status(): EmploymentStatus
    {
        return $this->status;
    }

    public function remoteWorkPolicy(): ?RemoteWorkPolicy
    {
        return $this->remoteWorkPolicy;
    }

    public function terminationDate(): ?DateTimeImmutable
    {
        return $this->terminationDate;
    }
}

