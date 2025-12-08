<?php

declare(strict_types=1);

namespace Domain\Employee\ValueObjects;

use InvalidArgumentException;

/**
 * Position value object
 */
final readonly class Position
{
    private const VALID_POSITIONS = [
        'Junior Developer',
        'Developer',
        'Senior Developer',
        'Lead Developer',
        'Principal Developer',
        'Staff Engineer',
        'Junior QA Engineer',
        'QA Engineer',
        'Senior QA Engineer',
        'QA Lead',
        'Junior DevOps Engineer',
        'DevOps Engineer',
        'Senior DevOps Engineer',
        'DevOps Lead',
        'Junior Designer',
        'Designer',
        'Senior Designer',
        'Design Lead',
        'Product Manager',
        'Senior Product Manager',
        'Engineering Manager',
        'Senior Engineering Manager',
        'Director of Engineering',
        'VP of Engineering',
        'CTO',
    ];

    private function __construct(
        private string $title
    ) {
        $this->validate($title);
    }

    public static function fromString(string $title): self
    {
        return new self($title);
    }

    private function validate(string $title): void
    {
        if (!in_array($title, self::VALID_POSITIONS, true)) {
            throw new InvalidArgumentException("Invalid position: {$title}");
        }
    }

    public function title(): string
    {
        return $this->title;
    }

    public function level(): string
    {
        if (str_starts_with($this->title, 'Junior')) return 'Junior';
        if (str_starts_with($this->title, 'Senior')) return 'Senior';
        if (str_starts_with($this->title, 'Lead')) return 'Lead';
        if (str_starts_with($this->title, 'Principal') || str_starts_with($this->title, 'Staff')) return 'Principal';
        if (str_contains($this->title, 'Manager') || str_contains($this->title, 'Director') || str_contains($this->title, 'VP') || $this->title === 'CTO') return 'Management';
        
        return 'Mid';
    }

    public function department(): string
    {
        if (str_contains($this->title, 'Developer') || str_contains($this->title, 'Engineer')) return 'Engineering';
        if (str_contains($this->title, 'QA')) return 'QA';
        if (str_contains($this->title, 'DevOps')) return 'DevOps';
        if (str_contains($this->title, 'Designer')) return 'Design';
        if (str_contains($this->title, 'Product')) return 'Product';
        if (str_contains($this->title, 'Manager') || str_contains($this->title, 'Director') || str_contains($this->title, 'VP') || $this->title === 'CTO') return 'Management';
        
        return 'Other';
    }

    public function isManagerial(): bool
    {
        return str_contains($this->title, 'Manager') 
            || str_contains($this->title, 'Director')
            || str_contains($this->title, 'VP')
            || $this->title === 'CTO';
    }

    public function equals(Position $other): bool
    {
        return $this->title === $other->title;
    }

    public function __toString(): string
    {
        return $this->title;
    }
}

