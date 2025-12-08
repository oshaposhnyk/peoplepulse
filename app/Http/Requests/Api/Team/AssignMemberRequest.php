<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Team;

use Illuminate\Foundation\Http\FormRequest;

class AssignMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'employeeId' => ['required', 'string', 'exists:employees,employee_id'],
            'role' => ['nullable', 'in:Member,TeamLead,TechLead'],
            'allocationPercentage' => ['nullable', 'integer', 'between:1,100'],
        ];
    }
}

