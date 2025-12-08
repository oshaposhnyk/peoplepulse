<?php

declare(strict_types=1);

namespace Application\Listeners\Leave;

use Application\Listeners\DomainEventListener;
use Domain\Leave\Events\LeaveApproved;
use Domain\Shared\Interfaces\DomainEvent;

/**
 * Deduct leave balance when leave is approved
 */
final class DeductLeaveBalanceListener extends DomainEventListener
{
    public function handle(DomainEvent $event): void
    {
        if (!$event instanceof LeaveApproved) {
            return;
        }

        // TODO: Deduct from leave balance
        
        logger()->info('Leave balance deduction triggered', [
            'leaveId' => $event->aggregateId(),
            'employeeId' => $event->employeeId(),
        ]);
    }

    public function viaQueue(): string
    {
        return 'default';
    }
}

