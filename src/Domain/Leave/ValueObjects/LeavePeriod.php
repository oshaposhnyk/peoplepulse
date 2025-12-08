<?php

declare(strict_types=1);

namespace Domain\Leave\ValueObjects;

use DateTimeImmutable;
use Domain\Shared\ValueObjects\DateRange;

/**
 * Leave period value object
 */
final readonly class LeavePeriod
{
    private function __construct(
        private DateRange $dateRange
    ) {
    }

    public static function fromDates(DateTimeImmutable $startDate, DateTimeImmutable $endDate): self
    {
        return new self(DateRange::fromDates($startDate, $endDate));
    }

    public function startDate(): DateTimeImmutable
    {
        return $this->dateRange->startDate();
    }

    public function endDate(): DateTimeImmutable
    {
        return $this->dateRange->endDate();
    }

    public function totalDays(): int
    {
        return $this->dateRange->durationInDays() + 1; // +1 to include both start and end dates
    }

    public function overlaps(LeavePeriod $other): bool
    {
        return $this->dateRange->overlaps($other->dateRange);
    }

    public function contains(DateTimeImmutable $date): bool
    {
        return $this->dateRange->contains($date);
    }
}

