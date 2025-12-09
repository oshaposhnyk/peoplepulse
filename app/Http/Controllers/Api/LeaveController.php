<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Leave\CreateLeaveRequest;
use App\Http\Requests\Api\Leave\ApproveLeaveRequest;
use App\Http\Requests\Api\Leave\RejectLeaveRequest;
use App\Http\Resources\LeaveResource;
use Application\DTOs\Leave\CreateLeaveRequestDTO;
use Application\Services\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Models\LeaveBalance;
use Infrastructure\Persistence\Eloquent\Models\LeaveRequest as LeaveRequestModel;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LeaveController extends Controller
{
    public function __construct(
        private LeaveService $leaveService
    ) {
    }

    /**
     * List leave requests
     */
    public function index(Request $request): JsonResponse
    {
        $query = LeaveRequestModel::query();

        // Non-admin can only see own requests
        if (!$request->user()->isAdmin()) {
            $query->where('employee_id', $request->user()->employee_id);
        }

        $leaves = QueryBuilder::for($query)
            ->allowedFilters([
                'leave_id',
                AllowedFilter::exact('leave_type'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('employee_id'),
            ])
            ->allowedSorts(['start_date', 'requested_at'])
            ->defaultSort('-requested_at')
            ->with(['employee', 'approver'])
            ->paginate($request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => LeaveResource::collection($leaves->items()),
            'meta' => [
                'currentPage' => $leaves->currentPage(),
                'perPage' => $leaves->perPage(),
                'total' => $leaves->total(),
                'lastPage' => $leaves->lastPage(),
            ],
        ]);
    }

    /**
     * Get single leave request
     */
    public function show(string $leaveId): JsonResponse
    {
        $leave = LeaveRequestModel::where('leave_id', $leaveId)
            ->with(['employee', 'backupPerson', 'approver', 'rejecter'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new LeaveResource($leave),
        ]);
    }

    /**
     * Request leave
     */
    public function store(CreateLeaveRequest $request): JsonResponse
    {
        // Admin can specify employeeId for other employees
        // Otherwise use current user's employee_id
        $employeeId = $request->input('employeeId') 
            ?: $request->user()->employee->employee_id;
        
        // Validate that non-admin users can only create leave for themselves
        if (!$request->user()->isAdmin() && $employeeId !== $request->user()->employee->employee_id) {
            abort(403, 'You can only create leave requests for yourself');
        }
        
        $dto = CreateLeaveRequestDTO::fromArray($request->validated(), $employeeId);
        
        $leaveId = $this->leaveService->requestLeave($dto);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $leaveId,
            ],
            'message' => 'Leave request submitted successfully',
        ], 201);
    }

    /**
     * Approve leave
     */
    public function approve(ApproveLeaveRequest $request, string $leaveId): JsonResponse
    {
        $approvedBy = $request->user()->employee->employee_id;

        $this->leaveService->approve($leaveId, $approvedBy, $request->approvalNotes);

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved',
        ]);
    }

    /**
     * Reject leave
     */
    public function reject(RejectLeaveRequest $request, string $leaveId): JsonResponse
    {
        $rejectedBy = $request->user()->employee->employee_id;

        $this->leaveService->reject($leaveId, $rejectedBy, $request->rejectionReason);

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected',
        ]);
    }

    /**
     * Cancel leave
     */
    public function cancel(string $leaveId): JsonResponse
    {
        $this->leaveService->cancel($leaveId);

        return response()->json([
            'success' => true,
            'message' => 'Leave request cancelled',
        ]);
    }

    /**
     * Get leave balance for employee
     */
    public function balance(Request $request, string $employeeId): JsonResponse
    {
        $employee = Employee::where('employee_id', $employeeId)->firstOrFail();
        
        // Non-admin can only see own balance
        if (!$request->user()->isAdmin() && $request->user()->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $year = $request->input('year', date('Y'));
        
        $balances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $year)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $year,
                'balances' => $balances->map(fn($b) => [
                    'leaveType' => $b->leave_type,
                    'opening' => (float) $b->opening_balance,
                    'accrued' => (float) $b->accrued,
                    'used' => (float) $b->used,
                    'pending' => (float) $b->pending,
                    'available' => (float) $b->available,
                    'carriedOver' => (float) $b->carried_over,
                ]),
            ],
        ]);
    }

    /**
     * Get leave calendar
     */
    public function calendar(Request $request): JsonResponse
    {
        $startDate = $request->input('startDate', date('Y-m-01'));
        $endDate = $request->input('endDate', date('Y-m-t'));

        $leaves = LeaveRequestModel::approved()
            ->inPeriod($startDate, $endDate)
            ->with('employee')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'leaves' => $leaves->map(fn($l) => [
                    'id' => $l->leave_id,
                    'employeeId' => $l->employee->employee_id,
                    'employeeName' => $l->employee->full_name,
                    'leaveType' => $l->leave_type,
                    'startDate' => $l->start_date->format('Y-m-d'),
                    'endDate' => $l->end_date->format('Y-m-d'),
                ]),
            ],
        ]);
    }
}

