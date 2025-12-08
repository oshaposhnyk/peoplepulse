<?php

declare(strict_types=1);

namespace Domain\Equipment\Events;

use Domain\Shared\DomainEvent;

final class EquipmentAdded extends DomainEvent
{
    public function __construct(
        private readonly string $equipmentId,
        private readonly string $assetTag,
        private readonly string $type,
        private readonly string $brand,
        private readonly string $model,
        private readonly string $serialNumber
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'equipment.added';
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
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'serialNumber' => $this->serialNumber,
        ]);
    }
}

