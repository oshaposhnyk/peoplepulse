<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->leave_id,
            'employeeId' => $this->employee->employee_id,
            'employeeName' => $this->employee->full_name,
            'employeePhotoUrl' => $this->employee->photo_url,
            'leaveType' => $this->leave_type,
            'startDate' => $this->start_date->format('Y-m-d'),
            'endDate' => $this->end_date->format('Y-m-d'),
            'totalDays' => (float) $this->total_days,
            'workingDays' => (float) $this->working_days,
            'reason' => $this->reason,
            'contactDuringLeave' => $this->contact_during_leave,
            
            'backupPerson' => $this->when($this->backupPerson, fn() => [
                'id' => $this->backupPerson->employee_id,
                'name' => $this->backupPerson->full_name,
            ]),
            
            'status' => $this->status,
            
            'approvedBy' => $this->when($this->approver, fn() => [
                'id' => $this->approver->employee_id,
                'name' => $this->approver->full_name,
            ]),
            'approvedAt' => $this->approved_at?->toIso8601String(),
            'approvalNotes' => $this->approval_notes,
            
            'rejectedBy' => $this->when($this->rejecter, fn() => [
                'id' => $this->rejecter->employee_id,
                'name' => $this->rejecter->full_name,
            ]),
            'rejectedAt' => $this->rejected_at?->toIso8601String(),
            'rejectionReason' => $this->rejection_reason,
            
            'cancelledAt' => $this->cancelled_at?->toIso8601String(),
            'cancellationReason' => $this->cancellation_reason,
            
            'requestedAt' => $this->requested_at->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}

