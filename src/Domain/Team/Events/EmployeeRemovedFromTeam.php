<?php

declare(strict_types=1);

namespace Domain\Team\Events;

use Domain\Shared\DomainEvent;

/**
 * Employee removed from team event
 */
final class EmployeeRemovedFromTeam extends DomainEvent
{
    public function __construct(
        private readonly string $teamId,
        private readonly string $employeeId
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'team.employee_removed';
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
        ]);
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }
}

