<?php

declare(strict_types=1);

namespace Application\Listeners\Employee;

use Application\Listeners\DomainEventListener;
use Domain\Employee\Events\EmployeeHired;
use Domain\Shared\Interfaces\DomainEvent;

/**
 * Create user account when employee is hired
 */
final class CreateUserAccountListener extends DomainEventListener
{
    public function handle(DomainEvent $event): void
    {
        if (!$event instanceof EmployeeHired) {
            return;
        }

        // TODO: Create user account
        // This will be implemented in Phase 4 (Authentication)
        
        logger()->info('User account creation triggered', [
            'employeeId' => $event->employeeId(),
            'email' => $event->personalInfo()->email()->value(),
        ]);
    }

    public function viaQueue(): string
    {
        return 'high-priority';
    }
}

