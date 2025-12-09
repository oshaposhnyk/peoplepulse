<?php

declare(strict_types=1);

namespace Application\Services;

use Application\DTOs\Leave\CreateLeaveRequestDTO;
use Application\Exceptions\NotFoundException;
use App\Support\Logging\AuditLogger;
use DateTimeImmutable;
use Domain\Leave\Aggregates\LeaveRequest;
use Domain\Leave\Repositories\LeaveRepositoryInterface;
use Domain\Leave\ValueObjects\LeaveId;
use Domain\Leave\ValueObjects\LeavePeriod;
use Domain\Leave\ValueObjects\LeaveType;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Infrastructure\Persistence\Eloquent\Models\LeaveBalance;
use Infrastructure\Persistence\Eloquent\Models\LeaveRequest as LeaveRequestModel;

class LeaveService extends BaseService
{
    public function __construct(
        private LeaveRepositoryInterface $repository
    ) {
    }

    /**
     * Request leave
     */
    public function requestLeave(CreateLeaveRequestDTO $dto): string
    {
        return $this->transaction(function () use ($dto) {
            $leaveId = $this->repository->nextIdentity();
            $employee = Employee::where('employee_id', $dto->employeeId)->firstOrFail();

            // Check if has sufficient balance (except sick/bereavement)
            $leaveType = LeaveType::fromString($dto->leaveType);
            if ($leaveType->requiresBalance()) {
                $balance = $this->getBalance($employee->id, $dto->leaveType, (int)date('Y'));
                $requestedDays = $this->calculateDays($dto->startDate, $dto->endDate);
                
                if (!$balance->hasSufficientBalance($requestedDays)) {
                    throw new \DomainException('Insufficient leave balance');
                }

                // Add to pending
                $balance->addToPending($requestedDays);
                $balance->save();
            }

            // Create leave request using domain
            $leave = LeaveRequest::request(
                LeaveId::fromString($leaveId),
                $dto->employeeId,
                $leaveType,
                LeavePeriod::fromDates(
                    new DateTimeImmutable($dto->startDate),
                    new DateTimeImmutable($dto->endDate)
                ),
                $dto->reason ?? ''
            );

            // Save using Eloquent
            $model = new LeaveRequestModel();
            $model->leave_id = $leaveId;
            $model->employee_id = $employee->id;
            $model->leave_type = $dto->leaveType;
            $model->start_date = $dto->startDate;
            $model->end_date = $dto->endDate;
            $model->total_days = $this->calculateDays($dto->startDate, $dto->endDate);
            $model->working_days = $model->total_days; // Simplified
            $model->reason = $dto->reason;
            $model->contact_during_leave = $dto->contactDuringLeave;
            $model->status = 'Pending';
            $model->requested_at = now();
            
            if ($dto->backupPersonId) {
                $backupPerson = Employee::where('employee_id', $dto->backupPersonId)->first();
                $model->backup_person_id = $backupPerson?->id;
            }
            
            $model->save();

            // Dispatch events
            foreach ($leave->releaseEvents() as $event) {
                event($event);
            }

            AuditLogger::leave('requested', $leaveId, [
                'employeeId' => $dto->employeeId,
                'type' => $dto->leaveType,
                'days' => $model->total_days,
            ]);

            return $leaveId;
        });
    }

    /**
     * Approve leave request
     */
    public function approve(string $leaveId, string $approvedBy, ?string $notes = null): void
    {
        $this->transaction(function () use ($leaveId, $approvedBy, $notes) {
            $model = LeaveRequestModel::where('leave_id', $leaveId)->firstOrFail();
            $approver = Employee::where('employee_id', $approvedBy)->firstOrFail();

            if ($model->status !== 'Pending') {
                throw new \DomainException('Can only approve pending leave requests');
            }

            $model->update([
                'status' => 'Approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            // Deduct from balance
            $year = $model->start_date instanceof \Carbon\Carbon ? $model->start_date->year : date('Y', strtotime($model->start_date));
            $balance = $this->getBalance($model->employee_id, $model->leave_type, $year);
            $balance->deduct((float)$model->total_days);
            $balance->save();

            AuditLogger::leave('approved', $leaveId, [
                'approvedBy' => $approvedBy,
                'days' => $model->total_days,
            ]);
        });
    }

    /**
     * Reject leave request
     */
    public function reject(string $leaveId, string $rejectedBy, string $reason): void
    {
        $this->transaction(function () use ($leaveId, $rejectedBy, $reason) {
            $model = LeaveRequestModel::where('leave_id', $leaveId)->firstOrFail();
            $rejecter = Employee::where('employee_id', $rejectedBy)->firstOrFail();

            if ($model->status !== 'Pending') {
                throw new \DomainException('Can only reject pending leave requests');
            }

            // Remove from pending
            $balance = $this->getBalance($model->employee_id, $model->leave_type, (int)date('Y'));
            $balance->removeFromPending((float)$model->total_days);
            $balance->save();

            $model->update([
                'status' => 'Rejected',
                'rejected_by' => $rejecter->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            AuditLogger::leave('rejected', $leaveId, [
                'rejectedBy' => $rejectedBy,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Cancel leave request
     */
    public function cancel(string $leaveId): void
    {
        $this->transaction(function () use ($leaveId) {
            $model = LeaveRequestModel::where('leave_id', $leaveId)->firstOrFail();

            if ($model->status === 'Completed') {
                throw new \DomainException('Cannot cancel completed leave');
            }

            $balance = $this->getBalance($model->employee_id, $model->leave_type, (int)date('Y'));

            // Restore balance if was approved
            if ($model->status === 'Approved') {
                $balance->restore((float)$model->total_days);
            } elseif ($model->status === 'Pending') {
                $balance->removeFromPending((float)$model->total_days);
            }
            
            $balance->save();

            $model->update([
                'status' => 'Cancelled',
                'cancelled_at' => now(),
            ]);

            AuditLogger::leave('cancelled', $leaveId);
        });
    }

    /**
     * Get or create leave balance
     */
    private function getBalance(int $employeeId, string $leaveType, int $year): LeaveBalance
    {
        return LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'year' => $year,
                'leave_type' => $leaveType,
            ],
            [
                'opening_balance' => 0,
                'accrual_rate' => $this->getAccrualRate($leaveType),
                'max_carry_over' => $this->getMaxCarryOver($leaveType),
            ]
        );
    }

    private function getAccrualRate(string $leaveType): float
    {
        return match($leaveType) {
            'Vacation' => 2.0,  // 2 days per month
            'Sick' => 1.0,      // 1 day per month
            'Personal' => 0.5,  // 0.5 days per month
            default => 0,
        };
    }

    private function getMaxCarryOver(string $leaveType): float
    {
        return match($leaveType) {
            'Vacation' => 5.0,
            default => 0,
        };
    }

    private function calculateDays(string $startDate, string $endDate): int
    {
        $start = new DateTimeImmutable($startDate);
        $end = new DateTimeImmutable($endDate);
        
        return $start->diff($end)->days + 1; // +1 to include both dates
    }

    public function findByEmployee(string $employeeId): array
    {
        return $this->repository->findByEmployee($employeeId);
    }

    public function findPending(): array
    {
        return $this->repository->findPending();
    }

    public function findApprovedInPeriod(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        return $this->repository->findApprovedInPeriod($startDate, $endDate);
    }

    public function hasOverlappingLeave(
        string $employeeId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): bool {
        return $this->repository->hasOverlappingLeave($employeeId, $startDate, $endDate);
    }
}

