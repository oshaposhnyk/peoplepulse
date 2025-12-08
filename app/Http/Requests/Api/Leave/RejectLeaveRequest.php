<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Leave;

use Illuminate\Foundation\Http\FormRequest;

class RejectLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'rejectionReason' => ['required', 'string'],
        ];
    }
}

