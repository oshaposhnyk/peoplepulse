<?php

declare(strict_types=1);

namespace Domain\Employee\ValueObjects;

use InvalidArgumentException;

/**
 * Work location value object
 */
final readonly class WorkLocation
{
    private const VALID_LOCATIONS = [
        'San Francisco HQ',
        'New York Office',
        'Austin Office',
        'London Office',
        'Remote',
        'Hybrid',
    ];

    private function __construct(
        private string $location
    ) {
        $this->validate($location);
    }

    public static function fromString(string $location): self
    {
        return new self($location);
    }

    private function validate(string $location): void
    {
        if (!in_array($location, self::VALID_LOCATIONS, true)) {
            throw new InvalidArgumentException("Invalid location: {$location}");
        }
    }

    public function location(): string
    {
        return $this->location;
    }

    public function isRemote(): bool
    {
        return $this->location === 'Remote';
    }

    public function isHybrid(): bool
    {
        return $this->location === 'Hybrid';
    }

    public function isOffice(): bool
    {
        return !in_array($this->location, ['Remote', 'Hybrid'], true);
    }

    public function equals(WorkLocation $other): bool
    {
        return $this->location === $other->location;
    }

    public function __toString(): string
    {
        return $this->location;
    }
}

