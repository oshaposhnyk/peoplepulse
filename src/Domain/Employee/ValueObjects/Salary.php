<?php

declare(strict_types=1);

namespace Domain\Employee\ValueObjects;

use Domain\Shared\ValueObjects\Money;
use InvalidArgumentException;

/**
 * Salary value object
 */
final readonly class Salary
{
    private const MIN_ANNUAL_SALARY = 30000;

    private function __construct(
        private Money $annualAmount,
        private string $payFrequency
    ) {
        $this->validate();
    }

    public static function fromMoney(Money $annualAmount, string $payFrequency = 'Annual'): self
    {
        return new self($annualAmount, $payFrequency);
    }

    private function validate(): void
    {
        if ($this->annualAmount->amount() < self::MIN_ANNUAL_SALARY) {
            throw new InvalidArgumentException(
                sprintf('Salary must be at least $%s/year', number_format(self::MIN_ANNUAL_SALARY))
            );
        }

        if (!in_array($this->payFrequency, ['Annual', 'Monthly', 'Biweekly'], true)) {
            throw new InvalidArgumentException("Invalid pay frequency: {$this->payFrequency}");
        }
    }

    public function amount(): float
    {
        return $this->annualAmount->amount();
    }

    public function currency(): string
    {
        return $this->annualAmount->currency();
    }

    public function annualAmount(): Money
    {
        return $this->annualAmount;
    }

    public function monthlyAmount(): Money
    {
        return $this->annualAmount->divide(12);
    }

    public function biweeklyAmount(): Money
    {
        return $this->annualAmount->divide(26);
    }

    public function payFrequency(): string
    {
        return $this->payFrequency;
    }

    public function canIncreaseTo(Salary $newSalary): bool
    {
        return $newSalary->amount() >= $this->amount();
    }

    public function equals(Salary $other): bool
    {
        return $this->annualAmount->equals($other->annualAmount)
            && $this->payFrequency === $other->payFrequency;
    }
}

