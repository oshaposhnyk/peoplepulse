<?php

declare(strict_types=1);

namespace Domain\Team\Events;

use Domain\Shared\DomainEvent;

/**
 * Team disbanded event
 */
final class TeamDisbanded extends DomainEvent
{
    public function __construct(
        private readonly string $teamId
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'team.disbanded';
    }

    public function aggregateId(): string
    {
        return $this->teamId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'teamId' => $this->teamId,
        ]);
    }
}

