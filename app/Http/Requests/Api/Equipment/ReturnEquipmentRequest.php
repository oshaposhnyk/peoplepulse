<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class ReturnEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'condition' => ['required', 'in:Good,Fair,Poor,Damaged'],
            'accessoriesReturned' => ['nullable', 'array'],
            'accessoriesReturned.*' => ['string'],
            'damageReport' => ['nullable', 'string'],
            'photos' => ['nullable', 'array'],
        ];
    }
}

