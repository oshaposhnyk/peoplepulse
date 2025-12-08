<?php

declare(strict_types=1);

namespace Domain\Team\ValueObjects;

use InvalidArgumentException;

/**
 * Team name value object
 */
final readonly class TeamName
{
    private const MAX_LENGTH = 100;

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
            throw new InvalidArgumentException('Team name cannot be empty');
        }

        if (strlen($value) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Team name too long (max %d characters)', self::MAX_LENGTH)
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(TeamName $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

