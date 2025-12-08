<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamMember extends Pivot
{
    protected $table = 'team_members';

    protected $fillable = [
        'team_id',
        'employee_id',
        'role',
        'allocation_percentage',
        'assigned_at',
        'removed_at',
        'assigned_by',
        'removed_by',
        'removal_reason',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'removed_at' => 'datetime',
        'allocation_percentage' => 'integer',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function isActive(): bool
    {
        return $this->removed_at === null;
    }
}

