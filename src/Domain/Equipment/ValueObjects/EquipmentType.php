<?php

declare(strict_types=1);

namespace Domain\Equipment\ValueObjects;

use InvalidArgumentException;

/**
 * Equipment type value object
 */
final readonly class EquipmentType
{
    private const VALID_TYPES = [
        'Laptop',
        'Desktop',
        'Monitor',
        'Keyboard',
        'Mouse',
        'Headset',
        'Phone',
        'Tablet',
        'Adapter',
        'Cable',
        'Dock',
        'Webcam',
    ];

    private function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function validate(string $value): void
    {
        if (!in_array($value, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException("Invalid equipment type: {$value}");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPrimaryDevice(): bool
    {
        return in_array($this->value, ['Laptop', 'Desktop', 'Phone', 'Tablet'], true);
    }

    public function isAccessory(): bool
    {
        return in_array($this->value, ['Keyboard', 'Mouse', 'Headset', 'Adapter', 'Cable', 'Webcam'], true);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

