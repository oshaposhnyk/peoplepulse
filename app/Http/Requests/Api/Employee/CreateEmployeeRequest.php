<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Employee;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'middleName' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:employees,email'],
            'phone' => ['required', 'string'],
            'dateOfBirth' => ['nullable', 'date', 'before:-18 years'],
            
            'address.street' => ['nullable', 'string'],
            'address.city' => ['nullable', 'string'],
            'address.state' => ['nullable', 'string'],
            'address.zipCode' => ['nullable', 'string'],
            'address.country' => ['nullable', 'string'],
            
            'position' => ['required', 'string'],
            'department' => ['nullable', 'string'],
            'employmentType' => ['nullable', 'in:Full-time,Part-time,Contract,Intern'],
            
            'salary.amount' => ['required', 'numeric', 'min:30000'],
            'salary.currency' => ['nullable', 'string', 'size:3'],
            'salary.frequency' => ['nullable', 'in:Annual,Monthly,Hourly'],
            
            'location' => ['required', 'string'],
            'hireDate' => ['required', 'date', 'before_or_equal:today'],
            'startDate' => ['nullable', 'date'],
        ];
    }
}

