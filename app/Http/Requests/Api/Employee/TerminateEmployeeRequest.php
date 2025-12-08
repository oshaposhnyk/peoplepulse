<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Employee;

use Illuminate\Foundation\Http\FormRequest;

class TerminateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terminationDate' => ['required', 'date', 'after_or_equal:today'],
            'lastWorkingDay' => ['required', 'date', 'before_or_equal:terminationDate'],
            'terminationType' => ['required', 'in:Resignation,Termination,Retirement,Contract End'],
            'reason' => ['required', 'string'],
        ];
    }
}

