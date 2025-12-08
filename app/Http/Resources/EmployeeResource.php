<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->employee_id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'middleName' => $this->middle_name,
            'fullName' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'dateOfBirth' => $this->date_of_birth?->format('Y-m-d'),
            
            'address' => $this->when($this->address_street, [
                'street' => $this->address_street,
                'city' => $this->address_city,
                'state' => $this->address_state,
                'zipCode' => $this->address_zip_code,
                'country' => $this->address_country,
            ]),
            
            'emergencyContact' => $this->when($this->emergency_contact_name, [
                'name' => $this->emergency_contact_name,
                'phone' => $this->emergency_contact_phone,
                'relationship' => $this->emergency_contact_relationship,
            ]),
            
            'position' => $this->position,
            'department' => $this->department,
            'employmentType' => $this->employment_type,
            'status' => $this->employment_status,
            
            // Show salary only if authorized
            'salary' => $this->when(
                $request->user()?->can('viewSalary', $this->resource),
                [
                    'amount' => (float) $this->salary_amount,
                    'currency' => $this->salary_currency,
                    'frequency' => $this->salary_frequency,
                ]
            ),
            
            'location' => $this->office_location,
            'workLocationType' => $this->work_location_type,
            
            'remoteWork' => $this->when($this->remote_work_enabled, [
                'enabled' => $this->remote_work_enabled,
                'policy' => $this->remote_work_policy,
            ]),
            
            'hireDate' => $this->hire_date->format('Y-m-d'),
            'startDate' => $this->start_date->format('Y-m-d'),
            'terminationDate' => $this->termination_date?->format('Y-m-d'),
            'lastWorkingDay' => $this->last_working_day?->format('Y-m-d'),
            'terminationType' => $this->termination_type,
            
            'photoUrl' => $this->photo_url,
            
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}

