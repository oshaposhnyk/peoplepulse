<?php

declare(strict_types=1);

namespace Domain\Employee\Events;

use Domain\Employee\ValueObjects\RemoteWorkPolicy;
use Domain\Shared\DomainEvent;

/**
 * Remote work configured event
 */
final class RemoteWorkConfigured extends DomainEvent
{
    public function __construct(
        private readonly string $employeeId,
        private readonly ?RemoteWorkPolicy $policy
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'employee.remote_work_configured';
    }

    public function aggregateId(): string
    {
        return $this->employeeId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'employeeId' => $this->employeeId,
            'enabled' => $this->policy !== null,
            'type' => $this->policy?->type(),
            'remoteDays' => $this->policy?->remoteDays() ?? [],
        ]);
    }
}

