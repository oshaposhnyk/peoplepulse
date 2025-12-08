<?php

declare(strict_types=1);

namespace Domain\Equipment\Events;

use DateTimeImmutable;
use Domain\Shared\DomainEvent;

final class EquipmentIssued extends DomainEvent
{
    public function __construct(
        private readonly string $equipmentId,
        private readonly string $assetTag,
        private readonly string $employeeId,
        private readonly DateTimeImmutable $issueDate
    ) {
        parent::__construct();
    }

    public function eventType(): string
    {
        return 'equipment.issued';
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
            'employeeId' => $this->employeeId,
            'issueDate' => $this->issueDate->format('Y-m-d'),
        ]);
    }

    public function employeeId(): string
    {
        return $this->employeeId;
    }
}

