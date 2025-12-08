<?php

declare(strict_types=1);

namespace Domain\Equipment\ValueObjects;

use InvalidArgumentException;

/**
 * Serial number value object
 */
final readonly class SerialNumber
{
    private function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function fromString(string $value): self
    {
        return new self(trim($value));
    }

    private function validate(string $value): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Serial number cannot be empty');
        }

        if (strlen($value) < 6) {
            throw new InvalidArgumentException('Serial number must be at least 6 characters');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(SerialNumber $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

