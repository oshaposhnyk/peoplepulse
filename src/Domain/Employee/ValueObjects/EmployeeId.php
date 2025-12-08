<?php

declare(strict_types=1);

namespace Domain\Employee\ValueObjects;

use InvalidArgumentException;

/**
 * Employee ID value object
 * 
 * Format: EMP-YYYY-XXXX
 */
final readonly class EmployeeId
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
        return new self(sprintf('EMP-%04d-%04d', $year, $sequence));
    }

    private function validate(string $value): void
    {
        if (!preg_match('/^EMP-\d{4}-\d{4}$/', $value)) {
            throw new InvalidArgumentException(
                "Invalid employee ID format: {$value}. Expected format: EMP-YYYY-XXXX"
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(EmployeeId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

