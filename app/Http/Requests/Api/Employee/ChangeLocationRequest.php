<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Employee;

use Illuminate\Foundation\Http\FormRequest;

class ChangeLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'newLocation' => ['required', 'string'],
            'effectiveDate' => ['required', 'date'],
            'reason' => ['required', 'string'],
        ];
    }
}

