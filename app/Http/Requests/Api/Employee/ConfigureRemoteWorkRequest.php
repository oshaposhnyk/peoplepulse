<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Employee;

use Illuminate\Foundation\Http\FormRequest;

class ConfigureRemoteWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:FullRemote,Hybrid,OfficeOnly'],
            'remoteDays' => ['required_if:type,Hybrid', 'array'],
            'remoteDays.*' => ['in:Monday,Tuesday,Wednesday,Thursday,Friday'],
        ];
    }
}

