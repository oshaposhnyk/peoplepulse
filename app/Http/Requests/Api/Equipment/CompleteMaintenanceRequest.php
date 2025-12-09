<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class CompleteMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'completedDate' => ['nullable', 'date', 'after_or_equal:scheduledDate'],
            'actualCost' => ['nullable', 'numeric', 'min:0'],
            'workPerformed' => ['nullable', 'string'],
            'partsReplaced' => ['nullable', 'array'],
            'partsReplaced.*' => ['string'],
            'warrantyWork' => ['nullable', 'boolean'],
            'maintenanceId' => ['nullable', 'exists:equipment_maintenance,id'],
        ];
    }
}

