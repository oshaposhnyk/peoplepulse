<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Employee\CreateEmployeeRequest;
use App\Http\Requests\Api\Employee\UpdateEmployeeRequest;
use App\Http\Requests\Api\Employee\ChangePositionRequest;
use App\Http\Requests\Api\Employee\ChangeLocationRequest;
use App\Http\Requests\Api\Employee\ConfigureRemoteWorkRequest;
use App\Http\Requests\Api\Employee\TerminateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use Application\DTOs\Employee\CreateEmployeeDTO;
use Application\DTOs\Employee\UpdateEmployeeDTO;
use Application\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService
    ) {
    }

    /**
     * List employees with filtering, sorting, pagination
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Employee::class);

        $employees = QueryBuilder::for(Employee::class)
            ->allowedFilters([
                'employee_id',
                'email',
                AllowedFilter::exact('employment_status'),
                AllowedFilter::exact('position'),
                AllowedFilter::exact('department'),
                AllowedFilter::exact('office_location'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['first_name', 'last_name', 'hire_date', 'position'])
            ->defaultSort('-hire_date')
            ->paginate(request('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => EmployeeResource::collection($employees->items()),
            'meta' => [
                'currentPage' => $employees->currentPage(),
                'perPage' => $employees->perPage(),
                'total' => $employees->total(),
                'lastPage' => $employees->lastPage(),
            ],
        ]);
    }

    /**
     * Get single employee
     */
    public function show(string $employeeId): JsonResponse
    {
        $employee = Employee::where('employee_id', $employeeId)->firstOrFail();

        $this->authorize('view', $employee);

        return response()->json([
            'success' => true,
            'data' => new EmployeeResource($employee),
        ]);
    }

    /**
     * Create new employee
     */
    public function store(CreateEmployeeRequest $request): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $dto = CreateEmployeeDTO::fromArray($request->validated());
        
        $employeeId = $this->employeeService->hire($dto);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $employeeId,
            ],
            'message' => 'Employee hired successfully',
        ], 201);
    }

    /**
     * Update employee
     */
    public function update(UpdateEmployeeRequest $request, string $employeeId): JsonResponse
    {
        $employee = Employee::where('employee_id', $employeeId)->firstOrFail();

        $this->authorize('update', $employee);

        $dto = UpdateEmployeeDTO::fromArray($request->validated());

        $this->employeeService->update($employeeId, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
        ]);
    }

    /**
     * Change employee position
     */
    public function changePosition(ChangePositionRequest $request, string $employeeId): JsonResponse
    {
        $employee = Employee::where('employee_id', $employeeId)->firstOrFail();

        $this->authorize('changePosition', $employee);

        $this->employeeService->changePosition(
            $employeeId,
            $request->newPosition,
            $request->newSalary,
            $request->effectiveDate,
            $request->reason
        );

        return response()->json([
            'success' => true,
            'message' => 'Position changed successfully',
        ]);
    }

    /**
     * Change employee location
     */
    public function changeLocation(ChangeLocationRequest $request, string $employeeId): JsonResponse
    {
        $employee = Employee::where('employee_id', $employeeId)->firstOrFail();

        $this->authorize('changeLocation', $employee);

        $this->employeeService->changeLocation(
            $employeeId,
            $request->newLocation,
            $request->effectiveDate,
            $request->reason
        );

        return response()->json([
            'success' => true,
            'message' => 'Location changed successfully',
        ]);
    }

    /**
     * Configure remote work
     */
    public function configureRemoteWork(ConfigureRemoteWorkRequest $request, string $employeeId): JsonResponse
    {
        $employee = Employee::where('employee_id', $employeeId)->firstOrFail();

        $this->authorize('update', $employee);

        $this->employeeService->configureRemoteWork($employeeId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Remote work configured successfully',
        ]);
    }

    /**
     * Terminate employee
     */
    public function terminate(TerminateEmployeeRequest $request, string $employeeId): JsonResponse
    {
        $employee = Employee::where('employee_id', $employeeId)->firstOrFail();

        $this->authorize('terminate', $employee);

        $this->employeeService->terminate(
            $employeeId,
            $request->terminationDate,
            $request->lastWorkingDay,
            $request->terminationType,
            $request->reason
        );

        return response()->json([
            'success' => true,
            'message' => 'Employee terminated successfully',
        ]);
    }

    /**
     * Get employee history
     */
    public function history(string $employeeId): JsonResponse
    {
        $employee = Employee::where('employee_id', $employeeId)
            ->with(['positionHistory', 'locationHistory'])
            ->firstOrFail();

        $this->authorize('view', $employee);

        return response()->json([
            'success' => true,
            'data' => [
                'positionHistory' => $employee->positionHistory,
                'locationHistory' => $employee->locationHistory,
            ],
        ]);
    }
}

