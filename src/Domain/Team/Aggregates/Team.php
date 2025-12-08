<?php

declare(strict_types=1);

namespace Domain\Team\Aggregates;

use Domain\Shared\AggregateRoot;
use Domain\Team\Entities\TeamMember;
use Domain\Team\Events\EmployeeAssignedToTeam;
use Domain\Team\Events\EmployeeRemovedFromTeam;
use Domain\Team\Events\TeamCreated;
use Domain\Team\Events\TeamDisbanded;
use Domain\Team\Events\TeamLeadChanged;
use Domain\Team\Exceptions\TeamSizeLimitExceededException;
use Domain\Team\ValueObjects\MemberRole;
use Domain\Team\ValueObjects\TeamId;
use Domain\Team\ValueObjects\TeamName;
use DomainException;

/**
 * Team aggregate root
 */
final class Team extends AggregateRoot
{
    /** @var array<TeamMember> */
    private array $members = [];

    private function __construct(
        private TeamId $id,
        private TeamName $name,
        private string $description,
        private string $type,
        private ?TeamId $parentTeamId,
        private ?int $maxSize,
        private bool $isDisbanded = false
    ) {
    }

    public static function create(
        TeamId $id,
        TeamName $name,
        string $description,
        string $type,
        ?TeamId $parentTeamId = null,
        ?int $maxSize = null
    ): self {
        $team = new self($id, $name, $description, $type, $parentTeamId, $maxSize);

        $team->recordEvent(new TeamCreated(
            $id->value(),
            $name->value(),
            $type
        ));

        return $team;
    }

    public function assignMember(
        string $employeeId,
        MemberRole $role,
        int $allocationPercentage = 100
    ): void {
        $this->ensureNotDisbanded();

        // Business rule: Cannot exceed max team size
        if ($this->maxSize && count($this->members) >= $this->maxSize) {
            throw new TeamSizeLimitExceededException(
                "Team {$this->name->value()} has reached maximum size of {$this->maxSize}"
            );
        }

        // Business rule: Cannot have duplicate members
        if ($this->hasMember($employeeId)) {
            throw new DomainException("Employee {$employeeId} is already a team member");
        }

        // Business rule: Can only have one team lead
        if ($role->isTeamLead() && $this->hasTeamLead()) {
            throw new DomainException('Team already has a team lead');
        }

        $member = TeamMember::create($employeeId, $role, $allocationPercentage);
        $this->members[] = $member;

        $this->recordEvent(new EmployeeAssignedToTeam(
            $this->id->value(),
            $employeeId,
            $role->value(),
            $allocationPercentage
        ));
    }

    public function removeMember(string $employeeId): void
    {
        $this->ensureNotDisbanded();

        if (!$this->hasMember($employeeId)) {
            throw new DomainException("Employee {$employeeId} is not a team member");
        }

        $this->members = array_filter(
            $this->members,
            fn(TeamMember $m) => $m->employeeId() !== $employeeId
        );

        $this->recordEvent(new EmployeeRemovedFromTeam(
            $this->id->value(),
            $employeeId
        ));
    }

    public function changeTeamLead(string $newLeadEmployeeId): void
    {
        $this->ensureNotDisbanded();

        // Business rule: New lead must be a team member
        if (!$this->hasMember($newLeadEmployeeId)) {
            throw new DomainException('New team lead must be a team member');
        }

        // Demote current lead
        foreach ($this->members as $member) {
            if ($member->role()->isTeamLead()) {
                $member->changeRole(MemberRole::member());
            }
        }

        // Promote new lead
        foreach ($this->members as $member) {
            if ($member->employeeId() === $newLeadEmployeeId) {
                $member->changeRole(MemberRole::teamLead());
            }
        }

        $this->recordEvent(new TeamLeadChanged(
            $this->id->value(),
            $newLeadEmployeeId
        ));
    }

    public function disband(): void
    {
        $this->ensureNotDisbanded();

        // Business rule: Cannot disband team with members
        if (!empty($this->members)) {
            throw new DomainException(
                'Cannot disband team with members. Remove all members first.'
            );
        }

        $this->isDisbanded = true;

        $this->recordEvent(new TeamDisbanded($this->id->value()));
    }

    public function memberCount(): int
    {
        return count($this->members);
    }

    private function hasMember(string $employeeId): bool
    {
        foreach ($this->members as $member) {
            if ($member->employeeId() === $employeeId) {
                return true;
            }
        }
        return false;
    }

    private function hasTeamLead(): bool
    {
        foreach ($this->members as $member) {
            if ($member->role()->isTeamLead()) {
                return true;
            }
        }
        return false;
    }

    private function ensureNotDisbanded(): void
    {
        if ($this->isDisbanded) {
            throw new DomainException("Team {$this->name->value()} has been disbanded");
        }
    }

    // Getters
    public function id(): string
    {
        return $this->id->value();
    }

    public function teamId(): TeamId
    {
        return $this->id;
    }

    public function name(): TeamName
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function parentTeamId(): ?TeamId
    {
        return $this->parentTeamId;
    }

    public function maxSize(): ?int
    {
        return $this->maxSize;
    }

    public function members(): array
    {
        return $this->members;
    }

    public function isDisbanded(): bool
    {
        return $this->isDisbanded;
    }
}

