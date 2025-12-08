<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Team;

use Illuminate\Foundation\Http\FormRequest;

class TransferEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'employeeId' => ['required', 'string', 'exists:employees,employee_id'],
            'targetTeamId' => ['required', 'string', 'exists:teams,team_id'],
            'newRole' => ['nullable', 'in:Member,TeamLead,TechLead'],
            'newAllocation' => ['nullable', 'integer', 'between:1,100'],
        ];
    }
}

