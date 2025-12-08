<?php

declare(strict_types=1);

namespace Application\Services;

use Application\DTOs\Team\CreateTeamDTO;
use Application\DTOs\Team\UpdateTeamDTO;
use Application\Exceptions\NotFoundException;
use App\Support\Logging\AuditLogger;
use Domain\Team\Aggregates\Team;
use Domain\Team\Repositories\TeamRepositoryInterface;
use Domain\Team\ValueObjects\MemberRole;
use Domain\Team\ValueObjects\TeamId;
use Domain\Team\ValueObjects\TeamName;
use Infrastructure\Persistence\Eloquent\Models\Team as TeamModel;

class TeamService extends BaseService
{
    public function __construct(
        private TeamRepositoryInterface $repository
    ) {
    }

    /**
     * Create a new team
     */
    public function create(CreateTeamDTO $dto): string
    {
        return $this->transaction(function () use ($dto) {
            $teamId = $this->repository->nextIdentity();

            $parentTeamId = $dto->parentTeamId 
                ? TeamId::fromString($dto->parentTeamId)
                : null;

            $team = Team::create(
                id: TeamId::fromString($teamId),
                name: TeamName::fromString($dto->name),
                description: $dto->description ?? '',
                type: $dto->type,
                parentTeamId: $parentTeamId,
                maxSize: $dto->maxSize
            );

            $this->repository->save($team);

            AuditLogger::team('created', $teamId, [
                'name' => $dto->name,
                'type' => $dto->type,
            ]);

            $this->logInfo('Team created', ['teamId' => $teamId]);

            return $teamId;
        });
    }

    /**
     * Update team
     */
    public function update(string $teamId, UpdateTeamDTO $dto): void
    {
        $this->transaction(function () use ($teamId, $dto) {
            $model = TeamModel::where('team_id', $teamId)->firstOrFail();

            $changes = [];

            if ($dto->name) {
                $model->name = $dto->name;
                $changes['name'] = $dto->name;
            }

            if ($dto->description !== null) {
                $model->description = $dto->description;
                $changes['description'] = $dto->description;
            }

            if ($dto->maxSize !== null) {
                $model->max_size = $dto->maxSize;
                $changes['maxSize'] = $dto->maxSize;
            }

            $model->save();

            if (!empty($changes)) {
                AuditLogger::team('updated', $teamId, $changes);
            }
        });
    }

    /**
     * Assign member to team
     */
    public function assignMember(
        string $teamId,
        string $employeeId,
        string $role = 'Member',
        int $allocation = 100
    ): void {
        $this->transaction(function () use ($teamId, $employeeId, $role, $allocation) {
            $team = $this->repository->findById($teamId);

            if (!$team) {
                throw NotFoundException::resource('Team', $teamId);
            }

            $team->assignMember(
                $employeeId,
                MemberRole::fromString($role),
                $allocation
            );

            $this->repository->save($team);

            AuditLogger::team('member_assigned', $teamId, [
                'employeeId' => $employeeId,
                'role' => $role,
            ]);
        });
    }

    /**
     * Remove member from team
     */
    public function removeMember(string $teamId, string $employeeId): void
    {
        $this->transaction(function () use ($teamId, $employeeId) {
            $team = $this->repository->findById($teamId);

            if (!$team) {
                throw NotFoundException::resource('Team', $teamId);
            }

            $team->removeMember($employeeId);

            $this->repository->save($team);

            AuditLogger::team('member_removed', $teamId, [
                'employeeId' => $employeeId,
            ]);
        });
    }

    /**
     * Transfer employee between teams
     */
    public function transfer(
        string $employeeId,
        string $sourceTeamId,
        string $targetTeamId,
        string $role = 'Member',
        int $allocation = 100
    ): void {
        $this->transaction(function () use ($employeeId, $sourceTeamId, $targetTeamId, $role, $allocation) {
            // Remove from source team
            $this->removeMember($sourceTeamId, $employeeId);

            // Add to target team
            $this->assignMember($targetTeamId, $employeeId, $role, $allocation);

            $this->logInfo('Employee transferred', [
                'employeeId' => $employeeId,
                'from' => $sourceTeamId,
                'to' => $targetTeamId,
            ]);
        });
    }

    /**
     * Change team lead
     */
    public function changeTeamLead(string $teamId, string $newLeadEmployeeId): void
    {
        $this->transaction(function () use ($teamId, $newLeadEmployeeId) {
            $team = $this->repository->findById($teamId);

            if (!$team) {
                throw NotFoundException::resource('Team', $teamId);
            }

            $team->changeTeamLead($newLeadEmployeeId);

            $this->repository->save($team);

            AuditLogger::team('lead_changed', $teamId, [
                'newLeadId' => $newLeadEmployeeId,
            ]);
        });
    }

    /**
     * Disband team
     */
    public function disband(string $teamId): void
    {
        $this->transaction(function () use ($teamId) {
            $team = $this->repository->findById($teamId);

            if (!$team) {
                throw NotFoundException::resource('Team', $teamId);
            }

            $team->disband();

            $this->repository->save($team);

            AuditLogger::team('disbanded', $teamId);
        });
    }

    /**
     * Get team by ID
     */
    public function findById(string $teamId): ?Team
    {
        return $this->repository->findById($teamId);
    }
}

