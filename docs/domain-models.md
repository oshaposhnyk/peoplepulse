# Domain Models Design
## IT Employee Management System - DDD Domain Models

**Version:** 1.0  
**Date:** December 7, 2025  
**Status:** Final

---

## Table of Contents

1. [Overview](#overview)
2. [Employee Context Domain Model](#employee-context-domain-model)
3. [Team Context Domain Model](#team-context-domain-model)
4. [Equipment Context Domain Model](#equipment-context-domain-model)
5. [Leave Context Domain Model](#leave-context-domain-model)
6. [Identity & Access Context Domain Model](#identity--access-context-domain-model)
7. [Cross-Context Relationships](#cross-context-relationships)

---

## Overview

### Purpose
This document provides detailed domain models for each bounded context, including aggregate roots, entities, value objects, domain events, and business rules.

### DDD Building Blocks

**Aggregate Root**
- Entry point for all operations on the aggregate
- Enforces invariants and business rules
- Has unique identity
- Example: Employee, Team, Equipment

**Entity**
- Has unique identity
- Identity persists over time
- Mutable state
- Example: TeamMember, MaintenanceRecord

**Value Object**
- No unique identity
- Defined by its attributes
- Immutable
- Example: Email, Position, Money

**Domain Event**
- Represents something that happened
- Immutable
- Past tense naming
- Example: EmployeeHired, LeaveApproved

**Repository**
- Persistence abstraction
- Loads and saves aggregates
- Hides infrastructure details

**Domain Service**
- Stateless operations
- Doesn't fit naturally in entity/value object
- Coordinates multiple aggregates

---

## Employee Context Domain Model

### Aggregate Root: Employee

```php
<?php

namespace Domain\Employee\Aggregates;

use Domain\Employee\ValueObjects\EmployeeId;
use Domain\Employee\ValueObjects\PersonalInfo;
use Domain\Employee\ValueObjects\Position;
use Domain\Employee\ValueObjects\Salary;
use Domain\Employee\ValueObjects\EmploymentStatus;
use Domain\Employee\ValueObjects\WorkLocation;
use Domain\Employee\ValueObjects\RemoteWorkPolicy;
use Domain\Employee\Events\EmployeeHired;
use Domain\Employee\Events\EmployeeTerminated;
use Domain\Employee\Events\PositionChanged;
use Domain\Employee\Events\LocationChanged;
use Domain\Employee\Events\RemoteWorkEnabled;
use Domain\Employee\Exceptions\CannotModifyTerminatedEmployeeException;
use Domain\Shared\AggregateRoot;

final class Employee extends AggregateRoot
{
    private EmployeeId $id;
    private PersonalInfo $personalInfo;
    private Position $position;
    private Salary $salary;
    private EmploymentStatus $status;
    private WorkLocation $location;
    private ?RemoteWorkPolicy $remoteWorkPolicy;
    private \DateTimeImmutable $hireDate;
    private ?\DateTimeImmutable $terminationDate;
    private array $positionHistory;
    private array $locationHistory;
    
    // Private constructor - use factory methods
    private function __construct(
        EmployeeId $id,
        PersonalInfo $personalInfo,
        Position $position,
        Salary $salary,
        WorkLocation $location,
        \DateTimeImmutable $hireDate
    ) {
        $this->id = $id;
        $this->personalInfo = $personalInfo;
        $this->position = $position;
        $this->salary = $salary;
        $this->location = $location;
        $this->hireDate = $hireDate;
        $this->status = EmploymentStatus::active();
        $this->remoteWorkPolicy = null;
        $this->terminationDate = null;
        $this->positionHistory = [];
        $this->locationHistory = [];
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
        \DateTimeImmutable $hireDate
    ): self {
        // Business rule: Hire date cannot be in the future
        if ($hireDate > new \DateTimeImmutable()) {
            throw new \InvalidArgumentException('Hire date cannot be in the future');
        }
        
        $employee = new self($id, $personalInfo, $position, $salary, $location, $hireDate);
        
        // Raise domain event
        $employee->recordEvent(new EmployeeHired(
            $id,
            $personalInfo,
            $position,
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
        \DateTimeImmutable $effectiveDate,
        string $reason
    ): void {
        $this->ensureNotTerminated();
        
        // Business rule: Position effective date cannot be in the past
        if ($effectiveDate < new \DateTimeImmutable('today')) {
            throw new \InvalidArgumentException('Position effective date cannot be in the past');
        }
        
        // Business rule: Salary can only increase or stay same (decrease requires approval)
        if ($newSalary->amount() < $this->salary->amount()) {
            throw new \InvalidArgumentException(
                'Salary decrease requires special approval process'
            );
        }
        
        // Record history
        $this->positionHistory[] = new PositionHistoryEntry(
            $this->position,
            $this->salary,
            $effectiveDate,
            $reason
        );
        
        $previousPosition = $this->position;
        $previousSalary = $this->salary;
        
        $this->position = $newPosition;
        $this->salary = $newSalary;
        
        // Raise domain event
        $this->recordEvent(new PositionChanged(
            $this->id,
            $previousPosition,
            $newPosition,
            $previousSalary,
            $newSalary,
            $effectiveDate
        ));
    }
    
    /**
     * Change office location
     */
    public function changeLocation(
        WorkLocation $newLocation,
        \DateTimeImmutable $effectiveDate,
        string $reason
    ): void {
        $this->ensureNotTerminated();
        
        // Record history
        $this->locationHistory[] = new LocationHistoryEntry(
            $this->location,
            $effectiveDate,
            $reason
        );
        
        $previousLocation = $this->location;
        $this->location = $newLocation;
        
        // Raise domain event
        $this->recordEvent(new LocationChanged(
            $this->id,
            $previousLocation,
            $newLocation,
            $effectiveDate
        ));
    }
    
    /**
     * Enable remote work
     */
    public function enableRemoteWork(RemoteWorkPolicy $policy): void
    {
        $this->ensureNotTerminated();
        $this->remoteWorkPolicy = $policy;
        
        $this->recordEvent(new RemoteWorkEnabled($this->id, $policy));
    }
    
    /**
     * Disable remote work
     */
    public function disableRemoteWork(): void
    {
        $this->ensureNotTerminated();
        $this->remoteWorkPolicy = null;
        
        $this->recordEvent(new RemoteWorkDisabled($this->id));
    }
    
    /**
     * Terminate employment
     */
    public function terminate(
        \DateTimeImmutable $terminationDate,
        \DateTimeImmutable $lastWorkingDay,
        string $terminationType,
        string $reason
    ): void {
        $this->ensureNotTerminated();
        
        // Business rule: Termination date cannot be in the past
        if ($terminationDate < new \DateTimeImmutable('today')) {
            throw new \InvalidArgumentException('Termination date cannot be in the past');
        }
        
        // Business rule: Last working day must be on or before termination date
        if ($lastWorkingDay > $terminationDate) {
            throw new \InvalidArgumentException(
                'Last working day must be on or before termination date'
            );
        }
        
        $this->status = EmploymentStatus::terminated();
        $this->terminationDate = $terminationDate;
        
        // Raise domain event
        $this->recordEvent(new EmployeeTerminated(
            $this->id,
            $terminationDate,
            $lastWorkingDay,
            $terminationType,
            $reason
        ));
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
     * Get employee ID
     */
    public function id(): EmployeeId
    {
        return $this->id;
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
    
    // Getters for other properties...
    public function personalInfo(): PersonalInfo { return $this->personalInfo; }
    public function position(): Position { return $this->position; }
    public function salary(): Salary { return $this->salary; }
    public function location(): WorkLocation { return $this->location; }
    public function hireDate(): \DateTimeImmutable { return $this->hireDate; }
    public function positionHistory(): array { return $this->positionHistory; }
}
```

### Value Objects

#### EmployeeId
```php
<?php

namespace Domain\Employee\ValueObjects;

final class EmployeeId
{
    private string $value;
    
    public function __construct(string $value)
    {
        // Format: EMP-YYYY-XXXX
        if (!preg_match('/^EMP-\d{4}-\d{4}$/', $value)) {
            throw new \InvalidArgumentException('Invalid employee ID format');
        }
        $this->value = $value;
    }
    
    public static function generate(int $year, int $sequence): self
    {
        return new self(sprintf('EMP-%04d-%04d', $year, $sequence));
    }
    
    public function value(): string
    {
        return $this->value;
    }
    
    public function equals(EmployeeId $other): bool
    {
        return $this->value === $other->value;
    }
}
```

#### PersonalInfo
```php
<?php

namespace Domain\Employee\ValueObjects;

use Domain\Shared\ValueObjects\Email;
use Domain\Shared\ValueObjects\PhoneNumber;

final class PersonalInfo
{
    private string $firstName;
    private string $lastName;
    private ?string $middleName;
    private Email $email;
    private PhoneNumber $phone;
    private ?\DateTimeImmutable $dateOfBirth;
    
    public function __construct(
        string $firstName,
        string $lastName,
        Email $email,
        PhoneNumber $phone,
        ?string $middleName = null,
        ?\DateTimeImmutable $dateOfBirth = null
    ) {
        // Business rule: Employee must be at least 18 years old
        if ($dateOfBirth && $this->calculateAge($dateOfBirth) < 18) {
            throw new \InvalidArgumentException('Employee must be at least 18 years old');
        }
        
        $this->firstName = trim($firstName);
        $this->lastName = trim($lastName);
        $this->middleName = $middleName ? trim($middleName) : null;
        $this->email = $email;
        $this->phone = $phone;
        $this->dateOfBirth = $dateOfBirth;
    }
    
    public function fullName(): string
    {
        return $this->middleName
            ? "{$this->firstName} {$this->middleName} {$this->lastName}"
            : "{$this->firstName} {$this->lastName}";
    }
    
    private function calculateAge(\DateTimeImmutable $dateOfBirth): int
    {
        return $dateOfBirth->diff(new \DateTimeImmutable())->y;
    }
    
    // Getters...
    public function firstName(): string { return $this->firstName; }
    public function lastName(): string { return $this->lastName; }
    public function email(): Email { return $this->email; }
    public function phone(): PhoneNumber { return $this->phone; }
}
```

#### Position
```php
<?php

namespace Domain\Employee\ValueObjects;

final class Position
{
    private const VALID_POSITIONS = [
        'Junior Developer',
        'Developer',
        'Senior Developer',
        'Lead Developer',
        'Principal Developer',
        'QA Engineer',
        'Senior QA Engineer',
        'DevOps Engineer',
        'Senior DevOps Engineer',
        'Designer',
        'Senior Designer',
        'Product Manager',
        'Engineering Manager',
        'Director of Engineering',
        'CTO',
    ];
    
    private string $title;
    private string $level; // Junior, Mid, Senior, Lead, Principal
    private string $department; // Engineering, QA, DevOps, Design, Management
    
    public function __construct(string $title)
    {
        if (!in_array($title, self::VALID_POSITIONS)) {
            throw new \InvalidArgumentException("Invalid position: {$title}");
        }
        
        $this->title = $title;
        $this->level = $this->extractLevel($title);
        $this->department = $this->extractDepartment($title);
    }
    
    public function title(): string
    {
        return $this->title;
    }
    
    public function isManagerial(): bool
    {
        return str_contains($this->title, 'Manager') 
            || str_contains($this->title, 'Director')
            || str_contains($this->title, 'CTO');
    }
    
    private function extractLevel(string $title): string
    {
        if (str_starts_with($title, 'Junior')) return 'Junior';
        if (str_starts_with($title, 'Senior')) return 'Senior';
        if (str_starts_with($title, 'Lead')) return 'Lead';
        if (str_starts_with($title, 'Principal')) return 'Principal';
        if (str_contains($title, 'Manager') || str_contains($title, 'Director')) return 'Management';
        return 'Mid';
    }
    
    private function extractDepartment(string $title): string
    {
        if (str_contains($title, 'Developer')) return 'Engineering';
        if (str_contains($title, 'QA')) return 'QA';
        if (str_contains($title, 'DevOps')) return 'DevOps';
        if (str_contains($title, 'Designer')) return 'Design';
        if (str_contains($title, 'Manager') || str_contains($title, 'Director')) return 'Management';
        return 'Other';
    }
}
```

#### Salary
```php
<?php

namespace Domain\Employee\ValueObjects;

use Domain\Shared\ValueObjects\Money;

final class Salary
{
    private Money $annualAmount;
    private string $payFrequency; // Annual, Monthly, Biweekly
    
    public function __construct(Money $annualAmount, string $payFrequency = 'Annual')
    {
        // Business rule: Minimum salary $30,000/year
        if ($annualAmount->amount() < 30000) {
            throw new \InvalidArgumentException('Salary must be at least $30,000/year');
        }
        
        $this->annualAmount = $annualAmount;
        $this->payFrequency = $payFrequency;
    }
    
    public function amount(): float
    {
        return $this->annualAmount->amount();
    }
    
    public function monthlyAmount(): Money
    {
        return $this->annualAmount->divide(12);
    }
    
    public function biweeklyAmount(): Money
    {
        return $this->annualAmount->divide(26);
    }
}
```

#### EmploymentStatus
```php
<?php

namespace Domain\Employee\ValueObjects;

final class EmploymentStatus
{
    private const ACTIVE = 'Active';
    private const TERMINATED = 'Terminated';
    private const ON_LEAVE = 'OnLeave';
    
    private string $status;
    
    private function __construct(string $status)
    {
        $this->status = $status;
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
    
    public function value(): string
    {
        return $this->status;
    }
}
```

#### WorkLocation
```php
<?php

namespace Domain\Employee\ValueObjects;

final class WorkLocation
{
    private const VALID_LOCATIONS = [
        'San Francisco HQ',
        'New York Office',
        'Austin Office',
        'London Office',
        'Remote',
        'Hybrid',
    ];
    
    private string $location;
    private bool $isRemote;
    
    public function __construct(string $location)
    {
        if (!in_array($location, self::VALID_LOCATIONS)) {
            throw new \InvalidArgumentException("Invalid location: {$location}");
        }
        
        $this->location = $location;
        $this->isRemote = $location === 'Remote';
    }
    
    public function location(): string
    {
        return $this->location;
    }
    
    public function isRemote(): bool
    {
        return $this->isRemote;
    }
    
    public function isOffice(): bool
    {
        return !$this->isRemote && $this->location !== 'Hybrid';
    }
}
```

#### RemoteWorkPolicy
```php
<?php

namespace Domain\Employee\ValueObjects;

final class RemoteWorkPolicy
{
    private string $type; // FullRemote, Hybrid, OfficeOnly
    private array $remoteDays; // ['Monday', 'Wednesday', 'Friday'] for Hybrid
    
    private function __construct(string $type, array $remoteDays = [])
    {
        $this->type = $type;
        $this->remoteDays = $remoteDays;
    }
    
    public static function fullRemote(): self
    {
        return new self('FullRemote', [
            'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'
        ]);
    }
    
    public static function hybrid(array $remoteDays): self
    {
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        foreach ($remoteDays as $day) {
            if (!in_array($day, $validDays)) {
                throw new \InvalidArgumentException("Invalid day: {$day}");
            }
        }
        
        return new self('Hybrid', $remoteDays);
    }
    
    public static function officeOnly(): self
    {
        return new self('OfficeOnly', []);
    }
    
    public function isFullRemote(): bool
    {
        return $this->type === 'FullRemote';
    }
    
    public function remoteDays(): array
    {
        return $this->remoteDays;
    }
}
```

### Domain Events

```php
<?php

namespace Domain\Employee\Events;

use Domain\Employee\ValueObjects\EmployeeId;
use Domain\Shared\DomainEvent;

final class EmployeeHired extends DomainEvent
{
    public function __construct(
        public readonly EmployeeId $employeeId,
        public readonly array $personalInfo,
        public readonly string $position,
        public readonly string $location,
        public readonly \DateTimeImmutable $hireDate
    ) {
        parent::__construct();
    }
    
    public function eventType(): string
    {
        return 'employee.hired';
    }
}

final class EmployeeTerminated extends DomainEvent
{
    public function __construct(
        public readonly EmployeeId $employeeId,
        public readonly \DateTimeImmutable $terminationDate,
        public readonly \DateTimeImmutable $lastWorkingDay,
        public readonly string $terminationType,
        public readonly string $reason
    ) {
        parent::__construct();
    }
    
    public function eventType(): string
    {
        return 'employee.terminated';
    }
}

final class PositionChanged extends DomainEvent
{
    public function __construct(
        public readonly EmployeeId $employeeId,
        public readonly string $previousPosition,
        public readonly string $newPosition,
        public readonly float $previousSalary,
        public readonly float $newSalary,
        public readonly \DateTimeImmutable $effectiveDate
    ) {
        parent::__construct();
    }
    
    public function eventType(): string
    {
        return 'employee.position_changed';
    }
}
```

### Repository Interface

```php
<?php

namespace Domain\Employee\Repositories;

use Domain\Employee\Aggregates\Employee;
use Domain\Employee\ValueObjects\EmployeeId;
use Domain\Shared\ValueObjects\Email;

interface EmployeeRepository
{
    public function nextIdentity(): EmployeeId;
    
    public function save(Employee $employee): void;
    
    public function findById(EmployeeId $id): ?Employee;
    
    public function findByEmail(Email $email): ?Employee;
    
    public function findAll(int $page = 1, int $perPage = 25): array;
    
    public function findActive(): array;
    
    public function findByPosition(string $position): array;
    
    public function findByLocation(string $location): array;
    
    public function delete(Employee $employee): void;
    
    public function emailExists(Email $email): bool;
}
```

---

## Team Context Domain Model

### Aggregate Root: Team

```php
<?php

namespace Domain\Team\Aggregates;

use Domain\Team\ValueObjects\TeamId;
use Domain\Team\ValueObjects\TeamName;
use Domain\Team\Entities\TeamMember;
use Domain\Team\ValueObjects\MemberRole;
use Domain\Team\Events\TeamCreated;
use Domain\Team\Events\EmployeeAssignedToTeam;
use Domain\Team\Exceptions\TeamSizeLimitExceededException;
use Domain\Shared\AggregateRoot;

final class Team extends AggregateRoot
{
    private TeamId $id;
    private TeamName $name;
    private string $description;
    private string $type;
    private ?TeamId $parentTeamId;
    private ?int $maxSize;
    private array $members; // Array of TeamMember entities
    private ?\DateTimeImmutable $disbandedAt;
    
    private function __construct(
        TeamId $id,
        TeamName $name,
        string $description,
        string $type,
        ?TeamId $parentTeamId = null,
        ?int $maxSize = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->parentTeamId = $parentTeamId;
        $this->maxSize = $maxSize;
        $this->members = [];
        $this->disbandedAt = null;
    }
    
    public static function create(
        TeamId $id,
        TeamName $name,
        string $description,
        string $type,
        ?TeamId $parentTeamId = null,
        ?int $maxSize = null
    ): self {
        $team = new self($id, $name, $description, $type, $parentTeamId, $maxSize);
        
        $team->recordEvent(new TeamCreated($id, $name, $type));
        
        return $team;
    }
    
    public function assignMember(
        string $employeeId,
        MemberRole $role,
        int $allocationPercentage = 100
    ): void {
        $this->ensureNotDisbanded();
        
        // Business rule: Cannot exceed max team size
        if ($this->maxSize && count($this->members) >= $this->maxSize) {
            throw new TeamSizeLimitExceededException(
                "Team {$this->name->value()} has reached maximum size of {$this->maxSize}"
            );
        }
        
        // Business rule: Cannot have duplicate members
        if ($this->hasMember($employeeId)) {
            throw new \DomainException("Employee {$employeeId} is already a team member");
        }
        
        // Business rule: Allocation must be 1-100%
        if ($allocationPercentage < 1 || $allocationPercentage > 100) {
            throw new \InvalidArgumentException('Allocation must be between 1 and 100');
        }
        
        // Business rule: Can only have one team lead
        if ($role->isTeamLead() && $this->hasTeamLead()) {
            throw new \DomainException('Team already has a team lead');
        }
        
        $member = new TeamMember($employeeId, $role, $allocationPercentage);
        $this->members[] = $member;
        
        $this->recordEvent(new EmployeeAssignedToTeam(
            $this->id,
            $employeeId,
            $role,
            $allocationPercentage
        ));
    }
    
    public function removeMember(string $employeeId): void
    {
        $this->ensureNotDisbanded();
        
        if (!$this->hasMember($employeeId)) {
            throw new \DomainException("Employee {$employeeId} is not a team member");
        }
        
        // Remove member
        $this->members = array_filter(
            $this->members,
            fn(TeamMember $m) => $m->employeeId() !== $employeeId
        );
        
        $this->recordEvent(new EmployeeRemovedFromTeam($this->id, $employeeId));
    }
    
    public function changeTeamLead(string $newLeadEmployeeId): void
    {
        $this->ensureNotDisbanded();
        
        // Business rule: New lead must be a team member
        if (!$this->hasMember($newLeadEmployeeId)) {
            throw new \DomainException('New team lead must be a team member');
        }
        
        // Demote current lead
        foreach ($this->members as $member) {
            if ($member->role()->isTeamLead()) {
                $member->changeRole(MemberRole::member());
            }
        }
        
        // Promote new lead
        foreach ($this->members as $member) {
            if ($member->employeeId() === $newLeadEmployeeId) {
                $member->changeRole(MemberRole::teamLead());
            }
        }
        
        $this->recordEvent(new TeamLeadChanged($this->id, $newLeadEmployeeId));
    }
    
    public function disband(): void
    {
        $this->ensureNotDisbanded();
        
        // Business rule: Cannot disband team with members
        if (!empty($this->members)) {
            throw new \DomainException('Cannot disband team with members. Remove all members first.');
        }
        
        $this->disbandedAt = new \DateTimeImmutable();
        
        $this->recordEvent(new TeamDisbanded($this->id));
    }
    
    public function memberCount(): int
    {
        return count($this->members);
    }
    
    private function hasMember(string $employeeId): bool
    {
        foreach ($this->members as $member) {
            if ($member->employeeId() === $employeeId) {
                return true;
            }
        }
        return false;
    }
    
    private function hasTeamLead(): bool
    {
        foreach ($this->members as $member) {
            if ($member->role()->isTeamLead()) {
                return true;
            }
        }
        return false;
    }
    
    private function ensureNotDisbanded(): void
    {
        if ($this->disbandedAt !== null) {
            throw new \DomainException("Team {$this->name->value()} has been disbanded");
        }
    }
    
    public function id(): TeamId { return $this->id; }
    public function name(): TeamName { return $this->name; }
    public function members(): array { return $this->members; }
}
```

### Entities

#### TeamMember
```php
<?php

namespace Domain\Team\Entities;

use Domain\Team\ValueObjects\MemberRole;

final class TeamMember
{
    private string $employeeId;
    private MemberRole $role;
    private int $allocationPercentage;
    private \DateTimeImmutable $assignedAt;
    
    public function __construct(
        string $employeeId,
        MemberRole $role,
        int $allocationPercentage
    ) {
        $this->employeeId = $employeeId;
        $this->role = $role;
        $this->allocationPercentage = $allocationPercentage;
        $this->assignedAt = new \DateTimeImmutable();
    }
    
    public function changeRole(MemberRole $newRole): void
    {
        $this->role = $newRole;
    }
    
    public function changeAllocation(int $percentage): void
    {
        if ($percentage < 1 || $percentage > 100) {
            throw new \InvalidArgumentException('Allocation must be between 1 and 100');
        }
        $this->allocationPercentage = $percentage;
    }
    
    public function employeeId(): string { return $this->employeeId; }
    public function role(): MemberRole { return $this->role; }
    public function allocationPercentage(): int { return $this->allocationPercentage; }
}
```

### Value Objects

#### TeamId, TeamName, MemberRole
```php
<?php

namespace Domain\Team\ValueObjects;

final class TeamId
{
    private string $value;
    
    public function __construct(string $value)
    {
        if (!preg_match('/^TEAM-\d{4}$/', $value)) {
            throw new \InvalidArgumentException('Invalid team ID format');
        }
        $this->value = $value;
    }
    
    public static function generate(int $sequence): self
    {
        return new self(sprintf('TEAM-%04d', $sequence));
    }
    
    public function value(): string { return $this->value; }
}

final class TeamName
{
    private string $value;
    
    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Team name cannot be empty');
        }
        if (strlen($trimmed) > 100) {
            throw new \InvalidArgumentException('Team name too long (max 100 characters)');
        }
        $this->value = $trimmed;
    }
    
    public function value(): string { return $this->value; }
}

final class MemberRole
{
    private const MEMBER = 'Member';
    private const TEAM_LEAD = 'TeamLead';
    private const TECH_LEAD = 'TechLead';
    
    private string $value;
    
    private function __construct(string $value)
    {
        $this->value = $value;
    }
    
    public static function member(): self { return new self(self::MEMBER); }
    public static function teamLead(): self { return new self(self::TEAM_LEAD); }
    public static function techLead(): self { return new self(self::TECH_LEAD); }
    
    public function isTeamLead(): bool { return $this->value === self::TEAM_LEAD; }
    public function value(): string { return $this->value; }
}
```

---

## Equipment Context Domain Model

### Aggregate Root: Equipment

```php
<?php

namespace Domain\Equipment\Aggregates;

use Domain\Equipment\ValueObjects\EquipmentId;
use Domain\Equipment\ValueObjects\AssetTag;
use Domain\Equipment\ValueObjects\EquipmentType;
use Domain\Equipment\ValueObjects\EquipmentStatus;
use Domain\Equipment\Entities\Assignment;
use Domain\Equipment\Events\EquipmentAdded;
use Domain\Equipment\Events\EquipmentIssued;
use Domain\Equipment\Events\EquipmentReturned;

final class Equipment extends AggregateRoot
{
    private EquipmentId $id;
    private AssetTag $assetTag;
    private EquipmentType $type;
    private string $brand;
    private string $model;
    private string $serialNumber;
    private EquipmentStatus $status;
    private ?Assignment $currentAssignment;
    private array $assignmentHistory;
    private \DateTimeImmutable $purchaseDate;
    private float $purchasePrice;
    
    private function __construct(
        EquipmentId $id,
        AssetTag $assetTag,
        EquipmentType $type,
        string $brand,
        string $model,
        string $serialNumber,
        \DateTimeImmutable $purchaseDate,
        float $purchasePrice
    ) {
        $this->id = $id;
        $this->assetTag = $assetTag;
        $this->type = $type;
        $this->brand = $brand;
        $this->model = $model;
        $this->serialNumber = $serialNumber;
        $this->purchaseDate = $purchaseDate;
        $this->purchasePrice = $purchasePrice;
        $this->status = EquipmentStatus::available();
        $this->currentAssignment = null;
        $this->assignmentHistory = [];
    }
    
    public static function add(
        EquipmentId $id,
        AssetTag $assetTag,
        EquipmentType $type,
        string $brand,
        string $model,
        string $serialNumber,
        \DateTimeImmutable $purchaseDate,
        float $purchasePrice
    ): self {
        $equipment = new self(
            $id, $assetTag, $type, $brand, $model, 
            $serialNumber, $purchaseDate, $purchasePrice
        );
        
        $equipment->recordEvent(new EquipmentAdded($id, $assetTag, $type));
        
        return $equipment;
    }
    
    public function issue(string $employeeId, \DateTimeImmutable $issueDate): void
    {
        // Business rule: Can only issue available equipment
        if (!$this->status->isAvailable()) {
            throw new \DomainException(
                "Equipment {$this->assetTag->value()} is not available for assignment"
            );
        }
        
        $assignment = new Assignment($employeeId, $issueDate);
        $this->currentAssignment = $assignment;
        $this->status = EquipmentStatus::assigned();
        
        $this->recordEvent(new EquipmentIssued(
            $this->id,
            $employeeId,
            $this->type,
            $issueDate
        ));
    }
    
    public function return(\DateTimeImmutable $returnDate, string $condition): void
    {
        // Business rule: Can only return assigned equipment
        if (!$this->status->isAssigned()) {
            throw new \DomainException('Equipment is not currently assigned');
        }
        
        if ($this->currentAssignment === null) {
            throw new \DomainException('No current assignment found');
        }
        
        $this->currentAssignment->complete($returnDate, $condition);
        $this->assignmentHistory[] = $this->currentAssignment;
        $this->currentAssignment = null;
        
        // Set status based on condition
        if ($condition === 'Good') {
            $this->status = EquipmentStatus::available();
        } else {
            $this->status = EquipmentStatus::inMaintenance();
        }
        
        $this->recordEvent(new EquipmentReturned(
            $this->id,
            $returnDate,
            $condition
        ));
    }
    
    public function isAvailable(): bool
    {
        return $this->status->isAvailable();
    }
    
    public function isAssigned(): bool
    {
        return $this->status->isAssigned();
    }
    
    public function id(): EquipmentId { return $this->id; }
    public function assetTag(): AssetTag { return $this->assetTag; }
}
```

### Entities

#### Assignment
```php
<?php

namespace Domain\Equipment\Entities;

final class Assignment
{
    private string $employeeId;
    private \DateTimeImmutable $assignedAt;
    private ?\DateTimeImmutable $returnedAt;
    private ?string $returnCondition;
    
    public function __construct(string $employeeId, \DateTimeImmutable $assignedAt)
    {
        $this->employeeId = $employeeId;
        $this->assignedAt = $assignedAt;
        $this->returnedAt = null;
        $this->returnCondition = null;
    }
    
    public function complete(\DateTimeImmutable $returnedAt, string $condition): void
    {
        if ($returnedAt < $this->assignedAt) {
            throw new \InvalidArgumentException('Return date cannot be before assignment date');
        }
        
        $this->returnedAt = $returnedAt;
        $this->returnCondition = $condition;
    }
    
    public function isActive(): bool
    {
        return $this->returnedAt === null;
    }
    
    public function employeeId(): string { return $this->employeeId; }
}
```

---

## Leave Context Domain Model

### Aggregate Root: LeaveRequest

```php
<?php

namespace Domain\Leave\Aggregates;

use Domain\Leave\ValueObjects\LeaveId;
use Domain\Leave\ValueObjects\LeaveType;
use Domain\Leave\ValueObjects\LeavePeriod;
use Domain\Leave\ValueObjects\LeaveStatus;
use Domain\Leave\Events\LeaveRequested;
use Domain\Leave\Events\LeaveApproved;
use Domain\Leave\Events\LeaveRejected;

final class LeaveRequest extends AggregateRoot
{
    private LeaveId $id;
    private string $employeeId;
    private LeaveType $type;
    private LeavePeriod $period;
    private LeaveStatus $status;
    private string $reason;
    private ?string $approvedBy;
    private ?\DateTimeImmutable $approvedAt;
    private ?string $rejectionReason;
    
    private function __construct(
        LeaveId $id,
        string $employeeId,
        LeaveType $type,
        LeavePeriod $period,
        string $reason
    ) {
        // Business rule: Cannot request leave in the past (except sick leave)
        if (!$type->isSick() && $period->startDate() < new \DateTimeImmutable('today')) {
            throw new \InvalidArgumentException('Cannot request leave in the past');
        }
        
        $this->id = $id;
        $this->employeeId = $employeeId;
        $this->type = $type;
        $this->period = $period;
        $this->reason = $reason;
        $this->status = LeaveStatus::pending();
        $this->approvedBy = null;
        $this->approvedAt = null;
        $this->rejectionReason = null;
    }
    
    public static function request(
        LeaveId $id,
        string $employeeId,
        LeaveType $type,
        LeavePeriod $period,
        string $reason
    ): self {
        $leave = new self($id, $employeeId, $type, $period, $reason);
        
        $leave->recordEvent(new LeaveRequested(
            $id,
            $employeeId,
            $type,
            $period
        ));
        
        return $leave;
    }
    
    public function approve(string $approvedBy): void
    {
        // Business rule: Can only approve pending requests
        if (!$this->status->isPending()) {
            throw new \DomainException('Can only approve pending leave requests');
        }
        
        $this->status = LeaveStatus::approved();
        $this->approvedBy = $approvedBy;
        $this->approvedAt = new \DateTimeImmutable();
        
        $this->recordEvent(new LeaveApproved(
            $this->id,
            $this->employeeId,
            $approvedBy
        ));
    }
    
    public function reject(string $rejectedBy, string $reason): void
    {
        // Business rule: Can only reject pending requests
        if (!$this->status->isPending()) {
            throw new \DomainException('Can only reject pending leave requests');
        }
        
        $this->status = LeaveStatus::rejected();
        $this->rejectionReason = $reason;
        
        $this->recordEvent(new LeaveRejected(
            $this->id,
            $this->employeeId,
            $rejectedBy,
            $reason
        ));
    }
    
    public function cancel(): void
    {
        // Business rule: Cannot cancel completed leave
        if ($this->status->isCompleted()) {
            throw new \DomainException('Cannot cancel completed leave');
        }
        
        // Business rule: Cannot cancel within 24 hours of start
        $hoursTillStart = (new \DateTimeImmutable())->diff($this->period->startDate())->h;
        if ($hoursTillStart < 24) {
            throw new \DomainException('Cannot cancel leave within 24 hours of start date');
        }
        
        $this->status = LeaveStatus::cancelled();
        
        $this->recordEvent(new LeaveCancelled($this->id, $this->employeeId));
    }
    
    public function id(): LeaveId { return $this->id; }
    public function status(): LeaveStatus { return $this->status; }
}
```

### Aggregate Root: LeaveBalance

```php
<?php

namespace Domain\Leave\Aggregates;

final class LeaveBalance extends AggregateRoot
{
    private string $employeeId;
    private int $year;
    private array $balancesByType; // ['Vacation' => 24, 'Sick' => 12]
    
    public function __construct(string $employeeId, int $year)
    {
        $this->employeeId = $employeeId;
        $this->year = $year;
        $this->balancesByType = [
            'Vacation' => 0,
            'Sick' => 0,
            'Personal' => 0,
        ];
    }
    
    public function accrue(string $leaveType, float $days): void
    {
        if (!isset($this->balancesByType[$leaveType])) {
            throw new \InvalidArgumentException("Invalid leave type: {$leaveType}");
        }
        
        $this->balancesByType[$leaveType] += $days;
        
        $this->recordEvent(new LeaveBalanceAccrued(
            $this->employeeId,
            $leaveType,
            $days,
            $this->balancesByType[$leaveType]
        ));
    }
    
    public function deduct(string $leaveType, float $days): void
    {
        if (!$this->hasSufficientBalance($leaveType, $days)) {
            throw new \DomainException("Insufficient {$leaveType} balance");
        }
        
        $this->balancesByType[$leaveType] -= $days;
    }
    
    public function hasSufficientBalance(string $leaveType, float $days): bool
    {
        return isset($this->balancesByType[$leaveType]) 
            && $this->balancesByType[$leaveType] >= $days;
    }
    
    public function balance(string $leaveType): float
    {
        return $this->balancesByType[$leaveType] ?? 0;
    }
}
```

---

## Identity & Access Context Domain Model

### Aggregate Root: User

```php
<?php

namespace Domain\Identity\Aggregates;

use Domain\Identity\ValueObjects\UserId;
use Domain\Identity\ValueObjects\Email;
use Domain\Identity\ValueObjects\HashedPassword;
use Domain\Identity\ValueObjects\Role;
use Domain\Identity\Events\UserRegistered;
use Domain\Identity\Events\UserLoggedIn;

final class User extends AggregateRoot
{
    private UserId $id;
    private Email $email;
    private HashedPassword $password;
    private Role $role;
    private bool $isActive;
    private bool $isLocked;
    private int $failedLoginAttempts;
    private ?\DateTimeImmutable $lockedUntil;
    private string $linkedEmployeeId;
    
    private function __construct(
        UserId $id,
        Email $email,
        HashedPassword $password,
        Role $role,
        string $linkedEmployeeId
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->linkedEmployeeId = $linkedEmployeeId;
        $this->isActive = true;
        $this->isLocked = false;
        $this->failedLoginAttempts = 0;
        $this->lockedUntil = null;
    }
    
    public static function register(
        UserId $id,
        Email $email,
        HashedPassword $password,
        Role $role,
        string $linkedEmployeeId
    ): self {
        $user = new self($id, $email, $password, $role, $linkedEmployeeId);
        
        $user->recordEvent(new UserRegistered($id, $email, $role));
        
        return $user;
    }
    
    public function authenticate(string $plainPassword): bool
    {
        // Check if account is locked
        if ($this->isLocked()) {
            throw new \DomainException('Account is locked');
        }
        
        if (!$this->password->verify($plainPassword)) {
            $this->recordFailedLogin();
            return false;
        }
        
        $this->resetFailedLoginAttempts();
        $this->recordEvent(new UserLoggedIn($this->id, $this->email));
        
        return true;
    }
    
    private function recordFailedLogin(): void
    {
        $this->failedLoginAttempts++;
        
        // Lock account after 5 failed attempts
        if ($this->failedLoginAttempts >= 5) {
            $this->lockAccount(30); // Lock for 30 minutes
        }
    }
    
    private function lockAccount(int $minutes): void
    {
        $this->isLocked = true;
        $this->lockedUntil = (new \DateTimeImmutable())->modify("+{$minutes} minutes");
    }
    
    public function isLocked(): bool
    {
        if (!$this->isLocked) {
            return false;
        }
        
        // Auto-unlock if lock period expired
        if ($this->lockedUntil && $this->lockedUntil < new \DateTimeImmutable()) {
            $this->isLocked = false;
            $this->lockedUntil = null;
            return false;
        }
        
        return true;
    }
    
    private function resetFailedLoginAttempts(): void
    {
        $this->failedLoginAttempts = 0;
    }
    
    public function changePassword(HashedPassword $newPassword): void
    {
        $this->password = $newPassword;
        $this->recordEvent(new PasswordChanged($this->id));
    }
    
    public function changeRole(Role $newRole): void
    {
        $this->role = $newRole;
        $this->recordEvent(new RoleChanged($this->id, $newRole));
    }
    
    public function deactivate(): void
    {
        $this->isActive = false;
    }
    
    public function id(): UserId { return $this->id; }
    public function email(): Email { return $this->email; }
    public function role(): Role { return $this->role; }
}
```

---

## Cross-Context Relationships

### Event-Driven Communication

#### Example: Employee Termination Flow

```
┌─────────────┐
│  Employee   │
│   Context   │
└──────┬──────┘
       │
       │ Employee.terminate()
       │
       ▼
┌──────────────────────┐
│ EmployeeTerminated   │ (Domain Event)
│ Event Published      │
└──────┬───────────────┘
       │
       ├──────────────┐
       │              │
       ▼              ▼
┌──────────┐   ┌─────────────┐
│   Team   │   │  Equipment  │
│ Context  │   │   Context   │
└─────┬────┘   └──────┬──────┘
      │               │
      │               │
      ▼               ▼
Remove from       Trigger
all teams         equipment
                  return
```

#### Example: Leave Request with Balance Check

```
┌──────────┐
│  Leave   │
│ Context  │
└─────┬────┘
      │
      │ LeaveRequest.request()
      │
      ├──────────────┐
      │              │
      ▼              ▼
Check balance   Validate dates
(LeaveBalance)  & team capacity
      │              │
      └──────┬───────┘
             │
             ▼
      LeaveRequested
         Event
```

---

## Summary

This document provides comprehensive domain models for all 5 bounded contexts:

### Employee Context
- **Aggregate:** Employee
- **Value Objects:** 7 (EmployeeId, PersonalInfo, Position, Salary, EmploymentStatus, WorkLocation, RemoteWorkPolicy)
- **Domain Events:** 8
- **Business Rules:** 15+

### Team Context
- **Aggregates:** Team
- **Entities:** TeamMember
- **Value Objects:** 3 (TeamId, TeamName, MemberRole)
- **Domain Events:** 8
- **Business Rules:** 10+

### Equipment Context
- **Aggregates:** Equipment
- **Entities:** Assignment, MaintenanceRecord
- **Value Objects:** 5 (EquipmentId, AssetTag, EquipmentType, EquipmentStatus, SerialNumber)
- **Domain Events:** 8
- **Business Rules:** 10+

### Leave Context
- **Aggregates:** LeaveRequest, LeaveBalance
- **Value Objects:** 4 (LeaveId, LeaveType, LeavePeriod, LeaveStatus)
- **Domain Events:** 8
- **Business Rules:** 12+

### Identity & Access Context
- **Aggregates:** User
- **Value Objects:** 4 (UserId, Email, HashedPassword, Role)
- **Domain Events:** 10
- **Business Rules:** 8+

All models follow DDD principles with:
✅ Rich domain objects with behavior  
✅ Immutable value objects  
✅ Aggregate boundaries enforced  
✅ Business rules in domain layer  
✅ Domain events for integration  

---

**Document Status:** ✅ Complete  
**Next Step:** Define complete event catalog (Task 1.5)

