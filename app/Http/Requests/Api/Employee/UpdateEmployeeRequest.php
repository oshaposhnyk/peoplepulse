<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employeeId');
        $employeeDbId = null;
        
        if ($employeeId) {
            $employee = \Infrastructure\Persistence\Eloquent\Models\Employee::where('employee_id', $employeeId)->first();
            $employeeDbId = $employee?->id;
        }
        
        return [
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:employees,email,' . ($employeeDbId ?? 'NULL') . ',id',
            ],
            'phone' => ['nullable', 'string'],
            'address.street' => ['nullable', 'string'],
            'address.city' => ['nullable', 'string'],
            'address.state' => ['nullable', 'string'],
            'address.zipCode' => ['nullable', 'string'],
            'address.country' => ['nullable', 'string'],
            'emergencyContact.name' => ['nullable', 'string'],
            'emergencyContact.phone' => ['nullable', 'string'],
            'emergencyContact.relationship' => ['nullable', 'string'],
            'photoUrl' => ['nullable', 'url'],
        ];
    }
}

