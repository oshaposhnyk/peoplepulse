<?php

declare(strict_types=1);

namespace Application\Listeners\Team;

use Application\Listeners\DomainEventListener;
use Domain\Shared\Interfaces\DomainEvent;
use Domain\Team\Events\EmployeeAssignedToTeam;
use Domain\Team\Events\EmployeeRemovedFromTeam;

/**
 * Update team capacity tracking when members change
 */
final class UpdateTeamCapacityListener extends DomainEventListener
{
    public function handle(DomainEvent $event): void
    {
        if ($event instanceof EmployeeAssignedToTeam || $event instanceof EmployeeRemovedFromTeam) {
            // TODO: Update team capacity for leave management
            
            logger()->info('Team capacity update triggered', [
                'teamId' => $event->aggregateId(),
                'eventType' => $event->eventType(),
            ]);
        }
    }
}

