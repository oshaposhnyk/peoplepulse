<?php

declare(strict_types=1);

namespace Domain\Equipment\Events;

use Domain\Shared\DomainEvent;

final class EquipmentDecommissioned extends DomainEvent
{
    public function __construct(
        private readonly string $equipmentId,
        private readonly string $assetTag,
        private readonly string $reason
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'equipment.decommissioned';
    }

    public function aggregateId(): string
    {
        return $this->equipmentId;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'equipmentId' => $this->equipmentId,
            'assetTag' => $this->assetTag,
            'reason' => $this->reason,
        ]);
    }
}

