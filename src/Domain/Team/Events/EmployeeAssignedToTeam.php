<?php

declare(strict_types=1);

namespace Domain\Team\Events;

use Domain\Shared\DomainEvent;

/**
 * Employee assigned to team event
 */
final class EmployeeAssignedToTeam extends DomainEvent
{
    public function __construct(
        private readonly string $teamId,
        private readonly string $employeeId,
        private readonly string $role,
        private readonly int $allocationPercentage
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'team.employee_assigned';
    }

    public function aggregateId(): string
    {
        return $this->teamId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'teamId' => $this->teamId,
            'employeeId' => $this->employeeId,
            'role' => $this->role,
            'allocationPercentage' => $this->allocationPercentage,
        ]);
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }
}

