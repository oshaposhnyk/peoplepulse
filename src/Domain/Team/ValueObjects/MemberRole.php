<?php

declare(strict_types=1);

namespace Domain\Team\ValueObjects;

/**
 * Team member role value object
 */
final readonly class MemberRole
{
    private const MEMBER = 'Member';
    private const TEAM_LEAD = 'TeamLead';
    private const TECH_LEAD = 'TechLead';

    private function __construct(
        private string $value
    ) {
    }

    public static function member(): self
    {
        return new self(self::MEMBER);
    }

    public static function teamLead(): self
    {
        return new self(self::TEAM_LEAD);
    }

    public static function techLead(): self
    {
        return new self(self::TECH_LEAD);
    }

    public static function fromString(string $value): self
    {
        return match($value) {
            self::MEMBER => self::member(),
            self::TEAM_LEAD => self::teamLead(),
            self::TECH_LEAD => self::techLead(),
            default => throw new \InvalidArgumentException("Invalid member role: {$value}")
        };
    }

    public function isTeamLead(): bool
    {
        return $this->value === self::TEAM_LEAD;
    }

    public function isTechLead(): bool
    {
        return $this->value === self::TECH_LEAD;
    }

    public function isMember(): bool
    {
        return $this->value === self::MEMBER;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(MemberRole $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

