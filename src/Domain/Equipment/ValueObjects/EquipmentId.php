<?php

declare(strict_types=1);

namespace Domain\Equipment\ValueObjects;

use InvalidArgumentException;

/**
 * Equipment ID value object
 */
final readonly class EquipmentId
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

    public static function generate(): self
    {
        return new self((string) \Illuminate\Support\Str::uuid());
    }

    private function validate(string $value): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Equipment ID cannot be empty');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(EquipmentId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

