<?php

declare(strict_types=1);

namespace Domain\Equipment\Events;

use DateTimeImmutable;
use Domain\Shared\DomainEvent;

final class EquipmentTransferred extends DomainEvent
{
    public function __construct(
        private readonly string $equipmentId,
        private readonly string $assetTag,
        private readonly string $fromEmployeeId,
        private readonly string $toEmployeeId,
        private readonly DateTimeImmutable $transferDate
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'equipment.transferred';
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
            'fromEmployeeId' => $this->fromEmployeeId,
            'toEmployeeId' => $this->toEmployeeId,
            'transferDate' => $this->transferDate->format('Y-m-d'),
        ]);
    }
}

