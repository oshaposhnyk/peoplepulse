<?php

declare(strict_types=1);

namespace Application\Listeners\Employee;

use Application\Listeners\DomainEventListener;
use Domain\Employee\Events\EmployeeTerminated;
use Domain\Shared\Interfaces\DomainEvent;

/**
 * Trigger offboarding process when employee is terminated
 */
final class TriggerOffboardingListener extends DomainEventListener
{
    public function handle(DomainEvent $event): void
    {
        if (!$event instanceof EmployeeTerminated) {
            return;
        }

        // TODO: Trigger multiple offboarding actions:
        // - Remove from all teams
        // - Trigger equipment return
        // - Disable user account
        // - Calculate final leave payout
        
        logger()->info('Offboarding process triggered', [
            'employeeId' => $event->employeeId(),
            'terminationDate' => $event->terminationDate()->format('Y-m-d'),
            'type' => $event->terminationType(),
        ]);
    }

    public function viaQueue(): string
    {
        return 'high-priority';
    }
}

