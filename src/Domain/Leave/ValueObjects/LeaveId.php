<?php

declare(strict_types=1);

namespace Domain\Leave\ValueObjects;

use InvalidArgumentException;

/**
 * Leave ID value object
 * 
 * Format: LEAVE-YYYY-XXXX
 */
final readonly class LeaveId
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
        return new self(sprintf('LEAVE-%04d-%04d', $year, $sequence));
    }

    private function validate(string $value): void
    {
        if (!preg_match('/^LEAVE-\d{4}-\d{4}$/', $value)) {
            throw new InvalidArgumentException(
                "Invalid leave ID format: {$value}. Expected format: LEAVE-YYYY-XXXX"
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

