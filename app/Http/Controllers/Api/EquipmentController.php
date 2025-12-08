<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Equipment\CreateEquipmentRequest;
use App\Http\Requests\Api\Equipment\IssueEquipmentRequest;
use App\Http\Requests\Api\Equipment\ReturnEquipmentRequest;
use App\Http\Requests\Api\Equipment\TransferEquipmentRequest;
use App\Http\Requests\Api\Equipment\DecommissionEquipmentRequest;
use App\Http\Resources\EquipmentResource;
use Application\DTOs\Equipment\CreateEquipmentDTO;
use Application\Services\EquipmentService;
use Illuminate\Http\JsonResponse;
use Infrastructure\Persistence\Eloquent\Models\Equipment;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EquipmentController extends Controller
{
    public function __construct(
        private EquipmentService $equipmentService
    ) {
    }

    /**
     * List equipment
     */
    public function index(): JsonResponse
    {
        $equipment = QueryBuilder::for(Equipment::class)
            ->allowedFilters([
                'asset_tag',
                'serial_number',
                AllowedFilter::exact('equipment_type'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('brand'),
            ])
            ->allowedSorts(['asset_tag', 'purchase_date', 'equipment_type'])
            ->defaultSort('-purchase_date')
            ->with(['currentAssignee'])
            ->paginate(request('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => EquipmentResource::collection($equipment->items()),
            'meta' => [
                'currentPage' => $equipment->currentPage(),
                'perPage' => $equipment->perPage(),
                'total' => $equipment->total(),
                'lastPage' => $equipment->lastPage(),
            ],
        ]);
    }

    /**
     * Get single equipment
     */
    public function show(string $equipmentId): JsonResponse
    {
        $equipment = Equipment::with(['currentAssignee', 'assignments', 'maintenanceRecords'])
            ->findOrFail($equipmentId);

        return response()->json([
            'success' => true,
            'data' => new EquipmentResource($equipment),
        ]);
    }

    /**
     * Add new equipment
     */
    public function store(CreateEquipmentRequest $request): JsonResponse
    {
        $dto = CreateEquipmentDTO::fromArray($request->validated());
        
        $equipmentId = $this->equipmentService->add($dto);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $equipmentId,
            ],
            'message' => 'Equipment added successfully',
        ], 201);
    }

    /**
     * Issue equipment to employee
     */
    public function issue(IssueEquipmentRequest $request, string $equipmentId): JsonResponse
    {
        $this->equipmentService->issue(
            $equipmentId,
            $request->employeeId,
            $request->accessories ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Equipment issued successfully',
        ]);
    }

    /**
     * Return equipment
     */
    public function return(ReturnEquipmentRequest $request, string $equipmentId): JsonResponse
    {
        $this->equipmentService->return(
            $equipmentId,
            $request->condition,
            $request->accessoriesReturned ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Equipment returned successfully',
        ]);
    }

    /**
     * Transfer equipment
     */
    public function transfer(TransferEquipmentRequest $request, string $equipmentId): JsonResponse
    {
        $this->equipmentService->transfer(
            $equipmentId,
            $request->toEmployeeId,
            $request->reason
        );

        return response()->json([
            'success' => true,
            'message' => 'Equipment transferred successfully',
        ]);
    }

    /**
     * Get equipment history
     */
    public function history(string $equipmentId): JsonResponse
    {
        $equipment = Equipment::with(['assignments.employee', 'maintenanceRecords', 'transfers'])
            ->findOrFail($equipmentId);

        return response()->json([
            'success' => true,
            'data' => [
                'assignments' => $equipment->assignments->map(fn($a) => [
                    'employeeId' => $a->employee->employee_id,
                    'employeeName' => $a->employee->full_name,
                    'assignedAt' => $a->assigned_at,
                    'returnedAt' => $a->returned_at,
                    'condition' => $a->condition_at_return ?? $a->condition_at_issue,
                ]),
                'maintenance' => $equipment->maintenanceRecords,
                'transfers' => $equipment->transfers,
            ],
        ]);
    }

    /**
     * Decommission equipment
     */
    public function destroy(DecommissionEquipmentRequest $request, string $equipmentId): JsonResponse
    {
        $this->equipmentService->decommission(
            $equipmentId,
            $request->reason,
            $request->disposalMethod
        );

        return response()->json([
            'success' => true,
            'message' => 'Equipment decommissioned successfully',
        ]);
    }
}

