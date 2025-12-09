<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Repositories;

use Domain\Shared\Interfaces\AggregateRoot;
use Domain\Team\Aggregates\Team as TeamAggregate;
use Domain\Team\Entities\TeamMember;
use Domain\Team\Repositories\TeamRepositoryInterface;
use Domain\Team\ValueObjects\MemberRole;
use Domain\Team\ValueObjects\TeamId;
use Domain\Team\ValueObjects\TeamName;
use Illuminate\Database\Eloquent\Model;
use Infrastructure\Persistence\BaseRepository;
use Infrastructure\Persistence\Eloquent\Models\Team as TeamModel;

class TeamRepository extends BaseRepository implements TeamRepositoryInterface
{
    protected function model(): string
    {
        return TeamModel::class;
    }

    public function nextIdentity(): string
    {
        $lastTeam = TeamModel::orderBy('team_id', 'desc')->first();

        $sequence = 1;
        if ($lastTeam) {
            $parts = explode('-', $lastTeam->team_id);
            $sequence = ((int) $parts[1]) + 1;
        }

        return TeamId::generate($sequence)->value();
    }

    protected function toDomain($model): mixed
    {
        /** @var TeamModel $model */
        
        $members = [];
        foreach ($model->members as $employee) {
            $members[] = TeamMember::create(
                $employee->employee_id,
                MemberRole::fromString($employee->pivot->role),
                $employee->pivot->allocation_percentage
            );
        }

        return new TeamAggregate(
            TeamId::fromString($model->team_id),
            TeamName::fromString($model->name),
            $model->description ?? '',
            $model->type,
            $model->parent_team_id ? TeamId::fromString($model->parent->team_id) : null,
            $model->max_size,
            !$model->is_active || $model->disbanded_at !== null,
            $members
        );
    }

    protected function toModel($aggregate): mixed
    {
        /** @var TeamAggregate $aggregate */
        
        $model = TeamModel::where('team_id', $aggregate->teamId()->value())->first()
            ?? new TeamModel();

        $model->team_id = $aggregate->teamId()->value();
        $model->name = $aggregate->name()->value();
        $model->description = $aggregate->description();
        $model->type = $aggregate->type();
        $model->department = $aggregate->type(); // TODO: separate department field
        $model->max_size = $aggregate->maxSize();
        $model->is_active = !$aggregate->isDisbanded();
        
        if ($aggregate->isDisbanded()) {
            $model->disbanded_at = now();
        }

        return $model;
    }

    public function findActive(): array
    {
        return TeamModel::active()
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function findByType(string $type): array
    {
        return TeamModel::where('type', $type)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function findByParent(TeamId $parentId): array
    {
        $parent = TeamModel::where('team_id', $parentId->value())->first();
        
        if (!$parent) {
            return [];
        }

        return TeamModel::where('parent_team_id', $parent->id)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    /**
     * Override findById to search by team_id (string) instead of id (integer)
     */
    public function findById(string $id): ?AggregateRoot
    {
        $model = TeamModel::where('team_id', $id)->first();
        return $model ? $this->toDomain($model) : null;
    }

    /**
     * Save team and sync members
     */
    public function save($aggregate): void
    {
        /** @var TeamAggregate $aggregate */
        
        $model = $this->toModel($aggregate);
        $model->save();

        // Sync team members
        $this->syncMembers($model, $aggregate->members());

        // Dispatch domain events
        $this->dispatchEvents($aggregate);
    }

    private function syncMembers(TeamModel $model, array $members): void
    {
        // Get current member IDs from model
        $currentMemberIds = $model->members->pluck('employee_id')->toArray();
        
        // Get domain member IDs
        $domainMemberIds = array_map(fn(TeamMember $m) => $m->employeeId(), $members);

        // Remove members not in domain
        foreach ($currentMemberIds as $currentId) {
            if (!in_array($currentId, $domainMemberIds)) {
                $model->members()->wherePivot('employee_id', $currentId)
                    ->update(['removed_at' => now()]);
            }
        }

        // Add/update members from domain
        foreach ($members as $member) {
            $employeeModel = \Infrastructure\Persistence\Eloquent\Models\Employee::where('employee_id', $member->employeeId())->first();
            
            if ($employeeModel) {
                $model->members()->syncWithoutDetaching([
                    $employeeModel->id => [
                        'role' => $member->role()->value(),
                        'allocation_percentage' => $member->allocationPercentage(),
                        'assigned_at' => $member->assignedAt(),
                    ]
                ]);
            }
        }
    }
}

