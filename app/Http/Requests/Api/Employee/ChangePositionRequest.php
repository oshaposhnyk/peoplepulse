<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Employee;

use Illuminate\Foundation\Http\FormRequest;

class ChangePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'newPosition' => ['required', 'string'],
            'newSalary' => ['required', 'numeric', 'min:30000'],
            'effectiveDate' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['required', 'string'],
        ];
    }
}

