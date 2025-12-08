<?php

declare(strict_types=1);

namespace Domain\Team\Events;

use Domain\Shared\DomainEvent;

/**
 * Team lead changed event
 */
final class TeamLeadChanged extends DomainEvent
{
    public function __construct(
        private readonly string $teamId,
        private readonly string $newLeadEmployeeId
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'team.lead_changed';
    }

    public function aggregateId(): string
    {
        return $this->teamId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'teamId' => $this->teamId,
            'newLeadEmployeeId' => $this->newLeadEmployeeId,
        ]);
    }
}

