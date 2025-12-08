<?php

declare(strict_types=1);

namespace Domain\Equipment\Aggregates;

use DateTimeImmutable;
use Domain\Equipment\Entities\Assignment;
use Domain\Equipment\Events\EquipmentAdded;
use Domain\Equipment\Events\EquipmentDecommissioned;
use Domain\Equipment\Events\EquipmentIssued;
use Domain\Equipment\Events\EquipmentReturned;
use Domain\Equipment\Events\EquipmentTransferred;
use Domain\Equipment\ValueObjects\AssetTag;
use Domain\Equipment\ValueObjects\EquipmentId;
use Domain\Equipment\ValueObjects\EquipmentStatus;
use Domain\Equipment\ValueObjects\EquipmentType;
use Domain\Equipment\ValueObjects\SerialNumber;
use Domain\Shared\AggregateRoot;
use Domain\Shared\ValueObjects\Money;
use DomainException;

/**
 * Equipment aggregate root
 */
final class Equipment extends AggregateRoot
{
    /** @var array<Assignment> */
    private array $assignmentHistory = [];

    public function __construct(
        EquipmentId $id,
        AssetTag $assetTag,
        EquipmentType $type,
        string $brand,
        string $model,
        SerialNumber $serialNumber,
        EquipmentStatus $status,
        DateTimeImmutable $purchaseDate,
        Money $purchasePrice,
        ?Assignment $currentAssignment = null,
        array $assignmentHistory = []
    ) {
        $this->id = $id;
        $this->assetTag = $assetTag;
        $this->type = $type;
        $this->brand = $brand;
        $this->model = $model;
        $this->serialNumber = $serialNumber;
        $this->status = $status;
        $this->purchaseDate = $purchaseDate;
        $this->purchasePrice = $purchasePrice;
        $this->currentAssignment = $currentAssignment;
        $this->assignmentHistory = $assignmentHistory;
    }

    public static function add(
        EquipmentId $id,
        AssetTag $assetTag,
        EquipmentType $type,
        string $brand,
        string $model,
        SerialNumber $serialNumber,
        DateTimeImmutable $purchaseDate,
        Money $purchasePrice
    ): self {
        $equipment = new self(
            $id,
            $assetTag,
            $type,
            $brand,
            $model,
            $serialNumber,
            EquipmentStatus::available(),
            $purchaseDate,
            $purchasePrice
        );

        $equipment->recordEvent(new EquipmentAdded(
            $id->value(),
            $assetTag->value(),
            $type->value(),
            $brand,
            $model,
            $serialNumber->value()
        ));

        return $equipment;
    }

    public function issue(string $employeeId, DateTimeImmutable $issueDate): void
    {
        if (!$this->status->isAvailable()) {
            throw new DomainException(
                "Equipment {$this->assetTag->value()} is not available for assignment"
            );
        }

        $assignment = Assignment::create($employeeId, $issueDate);
        $this->currentAssignment = $assignment;
        $this->status = EquipmentStatus::assigned();

        $this->recordEvent(new EquipmentIssued(
            $this->id->value(),
            $this->assetTag->value(),
            $employeeId,
            $issueDate
        ));
    }

    public function return(DateTimeImmutable $returnDate, string $condition): void
    {
        if (!$this->status->isAssigned()) {
            throw new DomainException('Equipment is not currently assigned');
        }

        if ($this->currentAssignment === null) {
            throw new DomainException('No current assignment found');
        }

        $this->currentAssignment->complete($returnDate, $condition);
        $this->assignmentHistory[] = $this->currentAssignment;
        $this->currentAssignment = null;

        // Set status based on condition
        $this->status = match($condition) {
            'Good' => EquipmentStatus::available(),
            default => EquipmentStatus::inMaintenance(),
        };

        $this->recordEvent(new EquipmentReturned(
            $this->id->value(),
            $this->assetTag->value(),
            $returnDate,
            $condition
        ));
    }

    public function transfer(string $toEmployeeId, DateTimeImmutable $transferDate): void
    {
        if (!$this->status->isAssigned()) {
            throw new DomainException('Can only transfer assigned equipment');
        }

        $fromEmployeeId = $this->currentAssignment?->employeeId();

        // Complete current assignment and create new one
        $this->currentAssignment?->complete($transferDate, 'Good');
        if ($this->currentAssignment) {
            $this->assignmentHistory[] = $this->currentAssignment;
        }

        $this->currentAssignment = Assignment::create($toEmployeeId, $transferDate);

        $this->recordEvent(new EquipmentTransferred(
            $this->id->value(),
            $this->assetTag->value(),
            $fromEmployeeId ?? '',
            $toEmployeeId,
            $transferDate
        ));
    }

    public function decommission(string $reason): void
    {
        if ($this->status->isAssigned()) {
            throw new DomainException('Cannot decommission assigned equipment');
        }

        $this->status = EquipmentStatus::decommissioned();

        $this->recordEvent(new EquipmentDecommissioned(
            $this->id->value(),
            $this->assetTag->value(),
            $reason
        ));
    }

    public function id(): string
    {
        return $this->id->value();
    }

    public function equipmentId(): EquipmentId
    {
        return $this->id;
    }

    public function assetTag(): AssetTag
    {
        return $this->assetTag;
    }

    public function type(): EquipmentType
    {
        return $this->type;
    }

    public function status(): EquipmentStatus
    {
        return $this->status;
    }

    public function isAvailable(): bool
    {
        return $this->status->isAvailable();
    }

    public function isAssigned(): bool
    {
        return $this->status->isAssigned();
    }

    public function currentAssignment(): ?Assignment
    {
        return $this->currentAssignment;
    }
}

