<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $teamLead = $this->teamLead();

        return [
            'id' => $this->team_id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'department' => $this->department,
            'maxSize' => $this->max_size,
            'memberCount' => $this->member_count,
            'isActive' => $this->is_active,
            
            'teamLead' => $teamLead ? [
                'id' => $teamLead->employee_id,
                'name' => $teamLead->full_name,
                'position' => $teamLead->position,
            ] : null,
            
            'parentTeam' => $this->when($this->parent, fn() => [
                'id' => $this->parent->team_id,
                'name' => $this->parent->name,
            ]),
            
            'members' => $this->when(
                $request->input('include') === 'members',
                fn() => $this->members->map(fn($employee) => [
                    'employeeId' => $employee->employee_id,
                    'fullName' => $employee->full_name,
                    'position' => $employee->position,
                    'role' => $employee->pivot->role,
                    'allocation' => $employee->pivot->allocation_percentage,
                ])
            ),
            
            'children' => $this->when(
                $request->input('include') === 'children',
                fn() => $this->children->map(fn($child) => [
                    'id' => $child->team_id,
                    'name' => $child->name,
                    'memberCount' => $child->member_count,
                ])
            ),
            
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}

