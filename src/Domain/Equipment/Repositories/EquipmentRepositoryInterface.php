<?php

declare(strict_types=1);

namespace Domain\Equipment\Repositories;

use Domain\Equipment\Aggregates\Equipment;
use Domain\Equipment\ValueObjects\AssetTag;
use Domain\Equipment\ValueObjects\EquipmentId;
use Domain\Equipment\ValueObjects\SerialNumber;
use Domain\Shared\Interfaces\Repository;

/**
 * Equipment repository interface
 */
interface EquipmentRepositoryInterface extends Repository
{
    public function nextIdentity(): string;

    /**
     * @return Equipment|null
     */
    public function findById(string $id): mixed;

    public function findByAssetTag(AssetTag $assetTag): ?Equipment;

    public function findBySerialNumber(SerialNumber $serialNumber): ?Equipment;

    /**
     * Find all available equipment
     * 
     * @return array<Equipment>
     */
    public function findAvailable(): array;

    /**
     * Find equipment by type
     * 
     * @return array<Equipment>
     */
    public function findByType(string $type): array;

    /**
     * Find equipment assigned to employee
     * 
     * @return array<Equipment>
     */
    public function findByEmployee(string $employeeId): array;

}

