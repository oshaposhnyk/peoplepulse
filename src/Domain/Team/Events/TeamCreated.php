<?php

declare(strict_types=1);

namespace Domain\Team\Events;

use Domain\Shared\DomainEvent;

/**
 * Team created event
 */
final class TeamCreated extends DomainEvent
{
    public function __construct(
        private readonly string $teamId,
        private readonly string $name,
        private readonly string $type
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'team.created';
    }

    public function aggregateId(): string
    {
        return $this->teamId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'teamId' => $this->teamId,
            'name' => $this->name,
            'type' => $this->type,
        ]);
    }
}

