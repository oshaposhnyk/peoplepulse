<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Money value object
 * 
 * Represents monetary value with currency.
 */
final readonly class Money
{
    private function __construct(
        private float $amount,
        private string $currency
    ) {
        $this->validate($amount);
    }
    
    public static function fromAmount(float $amount, string $currency = 'USD'): self
    {
        return new self(round($amount, 2), strtoupper($currency));
    }
    
    private function validate(float $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative');
        }
    }
    
    public function amount(): float
    {
        return $this->amount;
    }
    
    public function currency(): string
    {
        return $this->currency;
    }
    
    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        
        return new self($this->amount + $other->amount, $this->currency);
    }
    
    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        
        return new self($this->amount - $other->amount, $this->currency);
    }
    
    public function multiply(float $multiplier): self
    {
        return new self($this->amount * $multiplier, $this->currency);
    }
    
    public function divide(float $divisor): self
    {
        if ($divisor === 0.0) {
            throw new InvalidArgumentException('Cannot divide by zero');
        }
        
        return new self($this->amount / $divisor, $this->currency);
    }
    
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount 
            && $this->currency === $other->currency;
    }
    
    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot perform operation on different currencies: {$this->currency} vs {$other->currency}"
            );
        }
    }
}

