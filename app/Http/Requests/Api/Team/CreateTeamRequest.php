<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Team;

use Illuminate\Foundation\Http\FormRequest;

class CreateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:teams,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'string'],
            'department' => ['nullable', 'string'],
            'parentTeamId' => ['nullable', 'string', 'exists:teams,team_id'],
            'maxSize' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

