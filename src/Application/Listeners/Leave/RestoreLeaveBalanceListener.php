<?php

declare(strict_types=1);

namespace Application\Listeners\Leave;

use Application\Listeners\DomainEventListener;
use Domain\Leave\Events\LeaveCancelled;
use Domain\Shared\Interfaces\DomainEvent;

/**
 * Restore leave balance when leave is cancelled
 */
final class RestoreLeaveBalanceListener extends DomainEventListener
{
    public function handle(DomainEvent $event): void
    {
        if (!$event instanceof LeaveCancelled) {
            return;
        }

        // TODO: Restore leave balance
        
        logger()->info('Leave balance restoration triggered', [
            'leaveId' => $event->aggregateId(),
            'employeeId' => $event->employeeId(),
            'daysToRestore' => $event->totalDays(),
        ]);
    }
}

