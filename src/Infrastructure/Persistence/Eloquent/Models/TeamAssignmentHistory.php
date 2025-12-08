<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class TeamAssignmentHistory extends Model
{
    public $timestamps = false;

    protected $table = 'team_assignment_history';

    protected $fillable = [
        'team_id',
        'employee_id',
        'role',
        'allocation_percentage',
        'assigned_at',
        'removed_at',
        'assignment_duration_days',
        'assignment_reason',
        'removal_reason',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'removed_at' => 'datetime',
        'allocation_percentage' => 'integer',
        'assignment_duration_days' => 'integer',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

