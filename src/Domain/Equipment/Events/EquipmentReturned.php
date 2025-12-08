<?php

declare(strict_types=1);

namespace Domain\Equipment\Events;

use DateTimeImmutable;
use Domain\Shared\DomainEvent;

final class EquipmentReturned extends DomainEvent
{
    public function __construct(
        private readonly string $equipmentId,
        private readonly string $assetTag,
        private readonly DateTimeImmutable $returnDate,
        private readonly string $condition
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'equipment.returned';
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
            'returnDate' => $this->returnDate->format('Y-m-d'),
            'condition' => $this->condition,
        ]);
    }
}

