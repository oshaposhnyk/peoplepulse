<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Email value object
 * 
 * Represents a valid email address.
 */
final readonly class Email
{
    private function __construct(
        private string $value
    ) {
        $this->validate($value);
    }
    
    public static function fromString(string $email): self
    {
        return new self(strtolower(trim($email)));
    }
    
    private function validate(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                "Invalid email address: {$email}"
            );
        }
    }
    
    public function value(): string
    {
        return $this->value;
    }
    
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
    
    public function __toString(): string
    {
        return $this->value;
    }
}

