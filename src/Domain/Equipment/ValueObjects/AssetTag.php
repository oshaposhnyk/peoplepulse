<?php

declare(strict_types=1);

namespace Domain\Equipment\ValueObjects;

use InvalidArgumentException;

/**
 * Asset tag value object
 * 
 * Format: ASSET-YYYY-XXXX
 */
final readonly class AssetTag
{
    private function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(int $year, int $sequence): self
    {
        return new self(sprintf('ASSET-%04d-%04d', $year, $sequence));
    }

    private function validate(string $value): void
    {
        if (!preg_match('/^ASSET-\d{4}-\d{4}$/', $value)) {
            throw new InvalidArgumentException(
                "Invalid asset tag format: {$value}. Expected format: ASSET-YYYY-XXXX"
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(AssetTag $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

