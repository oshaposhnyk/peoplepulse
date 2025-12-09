<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'maintenanceType' => ['nullable', 'string', 'in:Cleaning,Repair,Upgrade,Inspection,Other'],
            'description' => ['required', 'string', 'min:10'],
            'scheduledDate' => ['nullable', 'date', 'after_or_equal:today'],
            'expectedDuration' => ['nullable', 'integer', 'min:1'],
            'serviceProvider' => ['nullable', 'string', 'max:200'],
            'estimatedCost' => ['nullable', 'numeric', 'min:0'],
            // Support legacy fields from frontend
            'reason' => ['nullable', 'string', 'min:10'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Map legacy fields to new format
        if ($this->has('reason') && !$this->has('description')) {
            $this->merge(['description' => $this->input('reason')]);
        }
        
        if ($this->has('expected_return_date') && !$this->has('scheduledDate')) {
            $this->merge(['scheduledDate' => $this->input('expected_return_date')]);
        }
    }
}

