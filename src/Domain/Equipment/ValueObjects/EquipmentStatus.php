<?php

declare(strict_types=1);

namespace Domain\Equipment\ValueObjects;

/**
 * Equipment status value object
 */
final readonly class EquipmentStatus
{
    private const AVAILABLE = 'Available';
    private const ASSIGNED = 'Assigned';
    private const IN_MAINTENANCE = 'InMaintenance';
    private const DECOMMISSIONED = 'Decommissioned';

    private function __construct(
        private string $status
    ) {
    }

    public static function available(): self
    {
        return new self(self::AVAILABLE);
    }

    public static function assigned(): self
    {
        return new self(self::ASSIGNED);
    }

    public static function inMaintenance(): self
    {
        return new self(self::IN_MAINTENANCE);
    }

    public static function decommissioned(): self
    {
        return new self(self::DECOMMISSIONED);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::AVAILABLE;
    }

    public function isAssigned(): bool
    {
        return $this->status === self::ASSIGNED;
    }

    public function isInMaintenance(): bool
    {
        return $this->status === self::IN_MAINTENANCE;
    }

    public function isDecommissioned(): bool
    {
        return $this->status === self::DECOMMISSIONED;
    }

    public function value(): string
    {
        return $this->status;
    }

    public function equals(EquipmentStatus $other): bool
    {
        return $this->status === $other->status;
    }

    public function __toString(): string
    {
        return $this->status;
    }
}

