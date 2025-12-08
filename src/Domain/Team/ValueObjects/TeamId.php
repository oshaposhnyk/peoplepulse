<?php

declare(strict_types=1);

namespace Domain\Team\ValueObjects;

use InvalidArgumentException;

/**
 * Team ID value object
 * 
 * Format: TEAM-XXXX
 */
final readonly class TeamId
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

    public static function generate(int $sequence): self
    {
        return new self(sprintf('TEAM-%04d', $sequence));
    }

    private function validate(string $value): void
    {
        if (!preg_match('/^TEAM-\d{4}$/', $value)) {
            throw new InvalidArgumentException(
                "Invalid team ID format: {$value}. Expected format: TEAM-XXXX"
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(TeamId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

