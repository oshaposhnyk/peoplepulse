<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Date range value object
 */
final readonly class DateRange
{
    private function __construct(
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate
    ) {
        $this->validate();
    }

    public static function fromDates(DateTimeImmutable $start, DateTimeImmutable $end): self
    {
        return new self($start, $end);
    }

    private function validate(): void
    {
        if ($this->startDate > $this->endDate) {
            throw new InvalidArgumentException('Start date must be before or equal to end date');
        }
    }

    public function startDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function overlaps(DateRange $other): bool
    {
        return $this->startDate <= $other->endDate 
            && $other->startDate <= $this->endDate;
    }

    public function contains(DateTimeImmutable $date): bool
    {
        return $date >= $this->startDate && $date <= $this->endDate;
    }

    public function durationInDays(): int
    {
        return $this->startDate->diff($this->endDate)->days;
    }
}

