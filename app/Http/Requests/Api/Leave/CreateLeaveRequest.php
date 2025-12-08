<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Leave;

use Illuminate\Foundation\Http\FormRequest;

class CreateLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // All authenticated users can request leave
    }

    public function rules(): array
    {
        return [
            'leaveType' => ['required', 'in:Vacation,Sick,Unpaid,Bereavement,Parental,Personal'],
            'startDate' => ['required', 'date', 'after_or_equal:today'],
            'endDate' => ['required', 'date', 'after:startDate'],
            'reason' => ['nullable', 'string'],
            'contactDuringLeave' => ['nullable', 'string'],
            'backupPersonId' => ['nullable', 'string', 'exists:employees,employee_id'],
        ];
    }
}

