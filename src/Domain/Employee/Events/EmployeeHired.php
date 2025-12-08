<?php

declare(strict_types=1);

namespace Domain\Employee\Events;

use DateTimeImmutable;
use Domain\Employee\ValueObjects\PersonalInfo;
use Domain\Employee\ValueObjects\Position;
use Domain\Employee\ValueObjects\Salary;
use Domain\Employee\ValueObjects\WorkLocation;
use Domain\Shared\DomainEvent;

/**
 * Employee hired event
 * 
 * Triggered when a new employee is hired.
 */
final class EmployeeHired extends DomainEvent
{
    public function __construct(
        private readonly string $employeeId,
        private readonly PersonalInfo $personalInfo,
        private readonly Position $position,
        private readonly Salary $salary,
        private readonly WorkLocation $location,
        private readonly DateTimeImmutable $hireDate
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'employee.hired';
    }

    public function aggregateId(): string
    {
        return $this->employeeId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'employeeId' => $this->employeeId,
            'firstName' => $this->personalInfo->firstName(),
            'lastName' => $this->personalInfo->lastName(),
            'email' => $this->personalInfo->email()->value(),
            'phone' => $this->personalInfo->phone()->value(),
            'position' => $this->position->title(),
            'department' => $this->position->department(),
            'salary' => [
                'amount' => $this->salary->amount(),
                'currency' => $this->salary->currency(),
            ],
            'location' => $this->location->location(),
            'hireDate' => $this->hireDate->format('Y-m-d'),
        ]);
    }

    // Getters
    public function employeeId(): string
    {
        return $this->employeeId;
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
}

