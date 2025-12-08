<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Phone number value object
 */
final readonly class PhoneNumber
{
    private function __construct(
        private string $value
    ) {
        $this->validate($value);
    }
    
    public static function fromString(string $phone): self
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        
        return new self($cleaned);
    }
    
    private function validate(string $phone): void
    {
        $digitsOnly = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($digitsOnly) < 10) {
            throw new InvalidArgumentException(
                'Phone number must have at least 10 digits'
            );
        }
    }
    
    public function value(): string
    {
        return $this->value;
    }
    
    public function formatted(): string
    {
        $digits = preg_replace('/[^0-9]/', '', $this->value);
        
        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s',
                substr($digits, 0, 3),
                substr($digits, 3, 3),
                substr($digits, 6)
            );
        }
        
        return $this->value;
    }
    
    public function __toString(): string
    {
        return $this->value;
    }
}

