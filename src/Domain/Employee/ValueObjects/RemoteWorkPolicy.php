<?php

declare(strict_types=1);

namespace Domain\Employee\ValueObjects;

use InvalidArgumentException;

/**
 * Remote work policy value object
 */
final readonly class RemoteWorkPolicy
{
    private const VALID_DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    private function __construct(
        private string $type,
        private array $remoteDays
    ) {
        $this->validate();
    }

    public static function fullRemote(): self
    {
        return new self('FullRemote', self::VALID_DAYS);
    }

    public static function hybrid(array $remoteDays): self
    {
        return new self('Hybrid', $remoteDays);
    }

    public static function officeOnly(): self
    {
        return new self('OfficeOnly', []);
    }

    private function validate(): void
    {
        foreach ($this->remoteDays as $day) {
            if (!in_array($day, self::VALID_DAYS, true)) {
                throw new InvalidArgumentException("Invalid day: {$day}");
            }
        }

        if ($this->type === 'Hybrid' && empty($this->remoteDays)) {
            throw new InvalidArgumentException('Hybrid policy must have at least one remote day');
        }
    }

    public function type(): string
    {
        return $this->type;
    }

    public function remoteDays(): array
    {
        return $this->remoteDays;
    }

    public function isFullRemote(): bool
    {
        return $this->type === 'FullRemote';
    }

    public function isHybrid(): bool
    {
        return $this->type === 'Hybrid';
    }

    public function isOfficeOnly(): bool
    {
        return $this->type === 'OfficeOnly';
    }

    public function remoteDaysPerWeek(): int
    {
        return count($this->remoteDays);
    }
}

