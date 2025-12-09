<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Team\CreateTeamRequest;
use App\Http\Requests\Api\Team\UpdateTeamRequest;
use App\Http\Requests\Api\Team\AssignMemberRequest;
use App\Http\Requests\Api\Team\TransferEmployeeRequest;
use App\Http\Requests\Api\Team\ChangeTeamLeadRequest;
use App\Http\Resources\TeamResource;
use Application\DTOs\Team\CreateTeamDTO;
use Application\DTOs\Team\UpdateTeamDTO;
use Application\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Infrastructure\Persistence\Eloquent\Models\Team;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TeamController extends Controller
{
    public function __construct(
        private TeamService $teamService
    ) {
    }

    /**
     * List teams
     */
    public function index(): JsonResponse
    {
        $teams = QueryBuilder::for(Team::class)
            ->allowedFilters([
                'team_id',
                AllowedFilter::exact('type'),
                AllowedFilter::exact('department'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['name', 'type', 'created_at'])
            ->defaultSort('name')
            ->with(['members', 'parent'])
            ->paginate(request('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => TeamResource::collection($teams->items()),
            'meta' => [
                'currentPage' => $teams->currentPage(),
                'perPage' => $teams->perPage(),
                'total' => $teams->total(),
                'lastPage' => $teams->lastPage(),
            ],
        ]);
    }

    /**
     * Get single team
     */
    public function show(string $teamId): JsonResponse
    {
        $team = Team::where('team_id', $teamId)
            ->with(['members', 'parent', 'children'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new TeamResource($team),
        ]);
    }

    /**
     * Create team
     */
    public function store(CreateTeamRequest $request): JsonResponse
    {
        $dto = CreateTeamDTO::fromArray($request->validated());
        
        $teamId = $this->teamService->create($dto);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $teamId,
            ],
            'message' => 'Team created successfully',
        ], 201);
    }

    /**
     * Update team
     */
    public function update(UpdateTeamRequest $request, string $teamId): JsonResponse
    {
        $dto = UpdateTeamDTO::fromArray($request->validated());

        $this->teamService->update($teamId, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Team updated successfully',
        ]);
    }

    /**
     * Assign member to team
     */
    public function assignMember(AssignMemberRequest $request, string $teamId): JsonResponse
    {
        $this->teamService->assignMember(
            $teamId,
            $request->employeeId,
            $request->role ?? 'Member',
            $request->allocationPercentage ?? 100
        );

        return response()->json([
            'success' => true,
            'message' => 'Member assigned to team successfully',
        ], 201);
    }

    /**
     * Remove member from team
     */
    public function removeMember(string $teamId, string $employeeId): JsonResponse
    {
        $this->teamService->removeMember($teamId, $employeeId);

        return response()->json([
            'success' => true,
            'message' => 'Member removed from team successfully',
        ]);
    }

    /**
     * Transfer employee between teams
     */
    public function transfer(TransferEmployeeRequest $request, string $teamId): JsonResponse
    {
        $this->teamService->transfer(
            $request->employeeId,
            $teamId,
            $request->targetTeamId,
            $request->newRole ?? 'Member',
            $request->newAllocation ?? 100
        );

        return response()->json([
            'success' => true,
            'message' => 'Employee transferred successfully',
        ]);
    }

    /**
     * Change team lead
     */
    public function changeTeamLead(ChangeTeamLeadRequest $request, string $teamId): JsonResponse
    {
        $this->teamService->changeTeamLead($teamId, $request->employeeId);

        return response()->json([
            'success' => true,
            'message' => 'Team lead changed successfully',
        ]);
    }

    /**
     * Get team members
     */
    public function members(string $teamId): JsonResponse
    {
        $team = Team::where('team_id', $teamId)
            ->with('members')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $team->members->map(function ($employee) {
                return [
                    'employeeId' => $employee->employee_id,
                    'firstName' => $employee->first_name,
                    'lastName' => $employee->last_name,
                    'fullName' => $employee->full_name,
                    'position' => $employee->position,
                    'email' => $employee->email,
                    'photoUrl' => $employee->photo_url,
                    'role' => $employee->pivot->role,
                    'allocationPercentage' => $employee->pivot->allocation_percentage,
                    'assignedAt' => $employee->pivot->assigned_at,
                ];
            }),
        ]);
    }

    /**
     * Disband team
     */
    public function destroy(string $teamId): JsonResponse
    {
        $this->teamService->disband($teamId);

        return response()->json([
            'success' => true,
            'message' => 'Team disbanded successfully',
        ]);
    }
}

