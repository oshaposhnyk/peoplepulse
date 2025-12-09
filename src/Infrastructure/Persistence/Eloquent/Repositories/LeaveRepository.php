<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Repositories;

use DateTimeImmutable;
use Domain\Leave\Aggregates\LeaveRequest as LeaveRequestAggregate;
use Domain\Leave\Repositories\LeaveRepositoryInterface;
use Domain\Leave\ValueObjects\LeaveId;
use Domain\Leave\ValueObjects\LeavePeriod;
use Domain\Leave\ValueObjects\LeaveStatus;
use Domain\Leave\ValueObjects\LeaveType;
use Domain\Shared\Interfaces\AggregateRoot;
use Illuminate\Database\Eloquent\Model;
use Infrastructure\Persistence\BaseRepository;
use Infrastructure\Persistence\Eloquent\Models\LeaveRequest as LeaveRequestModel;

class LeaveRepository extends BaseRepository implements LeaveRepositoryInterface
{
    protected function model(): string
    {
        return LeaveRequestModel::class;
    }

    public function nextIdentity(): string
    {
        $year = date('Y');
        $lastLeave = LeaveRequestModel::where('leave_id', 'like', "LEAVE-{$year}-%")
            ->orderBy('leave_id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastLeave) {
            $parts = explode('-', $lastLeave->leave_id);
            $sequence = ((int) $parts[2]) + 1;
        }

        return LeaveId::generate((int) $year, $sequence)->value();
    }

    protected function toDomain($model): mixed
    {
        /** @var LeaveRequestModel $model */
        
        return new LeaveRequestAggregate(
            LeaveId::fromString($model->leave_id),
            $model->employee->employee_id,
            LeaveType::fromString($model->leave_type),
            LeavePeriod::fromDates(
                new DateTimeImmutable($model->start_date->format('Y-m-d')),
                new DateTimeImmutable($model->end_date->format('Y-m-d'))
            ),
            $model->reason ?? '',
            match($model->status) {
                'Pending' => LeaveStatus::pending(),
                'Approved' => LeaveStatus::approved(),
                'Rejected' => LeaveStatus::rejected(),
                'Cancelled' => LeaveStatus::cancelled(),
                'Completed' => LeaveStatus::completed(),
            },
            $model->approver?->employee_id,
            $model->approved_at ? new DateTimeImmutable($model->approved_at->format('Y-m-d H:i:s')) : null,
            $model->rejection_reason
        );
    }

    protected function toModel($aggregate): mixed
    {
        /** @var LeaveRequestAggregate $aggregate */
        
        $employee = \Infrastructure\Persistence\Eloquent\Models\Employee::where('employee_id', $aggregate->employeeId())->first();

        $model = LeaveRequestModel::where('leave_id', $aggregate->leaveId()->value())->first()
            ?? new LeaveRequestModel();

        $model->leave_id = $aggregate->leaveId()->value();
        $model->employee_id = $employee->id;
        $model->leave_type = $aggregate->type()->value();
        $model->start_date = $aggregate->period()->startDate()->format('Y-m-d');
        $model->end_date = $aggregate->period()->endDate()->format('Y-m-d');
        $model->total_days = $aggregate->totalDays();
        $model->working_days = $aggregate->totalDays(); // Simplified
        $model->status = $aggregate->status()->value();

        return $model;
    }

    public function findByEmployee(string $employeeId): array
    {
        $employee = \Infrastructure\Persistence\Eloquent\Models\Employee::where('employee_id', $employeeId)->first();
        
        if (!$employee) {
            return [];
        }

        return LeaveRequestModel::where('employee_id', $employee->id)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function findPending(): array
    {
        return LeaveRequestModel::pending()
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function findApprovedInPeriod(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        return LeaveRequestModel::approved()
            ->inPeriod($startDate->format('Y-m-d'), $endDate->format('Y-m-d'))
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function hasOverlappingLeave(
        string $employeeId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): bool {
        $employee = \Infrastructure\Persistence\Eloquent\Models\Employee::where('employee_id', $employeeId)->first();
        
        if (!$employee) {
            return false;
        }

        return LeaveRequestModel::where('employee_id', $employee->id)
            ->approved()
            ->inPeriod($startDate->format('Y-m-d'), $endDate->format('Y-m-d'))
            ->exists();
    }
}

