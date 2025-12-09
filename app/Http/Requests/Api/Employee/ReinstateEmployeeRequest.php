<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Employee;

use Illuminate\Foundation\Http\FormRequest;

class ReinstateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reinstatementDate' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['required', 'string', 'min:10'],
        ];
    }
}

