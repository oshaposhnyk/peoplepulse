<?php

declare(strict_types=1);

namespace Domain\Leave\ValueObjects;

use InvalidArgumentException;

/**
 * Leave type value object
 */
final readonly class LeaveType
{
    private const VACATION = 'Vacation';
    private const SICK = 'Sick';
    private const UNPAID = 'Unpaid';
    private const BEREAVEMENT = 'Bereavement';
    private const PARENTAL = 'Parental';
    private const PERSONAL = 'Personal';

    private function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function vacation(): self
    {
        return new self(self::VACATION);
    }

    public static function sick(): self
    {
        return new self(self::SICK);
    }

    public static function unpaid(): self
    {
        return new self(self::UNPAID);
    }

    public static function bereavement(): self
    {
        return new self(self::BEREAVEMENT);
    }

    public static function parental(): self
    {
        return new self(self::PARENTAL);
    }

    public static function personal(): self
    {
        return new self(self::PERSONAL);
    }

    public static function fromString(string $value): self
    {
        return match($value) {
            self::VACATION => self::vacation(),
            self::SICK => self::sick(),
            self::UNPAID => self::unpaid(),
            self::BEREAVEMENT => self::bereavement(),
            self::PARENTAL => self::parental(),
            self::PERSONAL => self::personal(),
            default => throw new InvalidArgumentException("Invalid leave type: {$value}")
        };
    }

    private function validate(string $value): void
    {
        $valid = [self::VACATION, self::SICK, self::UNPAID, self::BEREAVEMENT, self::PARENTAL, self::PERSONAL];
        
        if (!in_array($value, $valid, true)) {
            throw new InvalidArgumentException("Invalid leave type: {$value}");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isVacation(): bool
    {
        return $this->value === self::VACATION;
    }

    public function isSick(): bool
    {
        return $this->value === self::SICK;
    }

    public function requiresBalance(): bool
    {
        return !in_array($this->value, [self::SICK, self::BEREAVEMENT], true);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

